<?php

namespace Tworzenieweb\SqlProvisioner\Command;

use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Finder\SplFileInfo;
use Tworzenieweb\SqlProvisioner\Config\ProvisionConfig;
use Tworzenieweb\SqlProvisioner\Controller\ProvisionDispatcher;
use Tworzenieweb\SqlProvisioner\Database\Connection;
use Tworzenieweb\SqlProvisioner\Filesystem\Exception;
use Tworzenieweb\SqlProvisioner\Filesystem\WorkingDirectory;
use Tworzenieweb\SqlProvisioner\Model\Candidate;
use Tworzenieweb\SqlProvisioner\Model\CandidateBuilder;
use Tworzenieweb\SqlProvisioner\Table\DataRowsBuilder;

/**
 * @author  Luke Adamczewski
 * @package Tworzenieweb\SqlProvisioner\Command
 */
class ProvisionCommand extends Command
{
    const HELP_MESSAGE = <<<'EOF'
The <info>%command.name% [path-to-folder]</info> command will scan the content of [path-to-folder] directory.
 
The script will look for <info>.env</info> file containing connection information in format:
<comment>
DATABASE_USER=[user]
DATABASE_PASSWORD=[password]
DATABASE_HOST=[host]
DATABASE_PORT=[port]
DATABASE_NAME=[database]
PROVISIONING_TABLE=changelog_database_deployments
PROVISIONING_TABLE_CANDIDATE_NUMBER_COLUMN=deploy_script_number
</comment>

If you want to create initial .env use <info>--init</info>

<info>%command.name% --init [path-to-folder]</info>

The next step is searching for sql files and trying to queue them in numerical order.
First n-th digits of a filename will be treated as candidate number. 
This will be used then to check in database if a certain file was already deployed (PROVISIONING_TABLE_CANDIDATE_NUMBER_COLUMN).
Before the insert, it will print the formatted output of a file and result of internal syntax check.
Then you can either skip or execute each.

If you would like to skip already provisioned candidates use <info>--skip-provisioned</info>
If you would like to skip syntax checking (for speed purpose) of candidates use <info>--skip-syntax-check</info>

EOF;

    /** @var Candidate[] */
    private $workingDirectoryCandidates = [];

    /** @var WorkingDirectory */
    private $workingDirectory;

    /** @var SymfonyStyle */
    private $io;

    /** @var Connection */
    private $connection;

    /** @var boolean */
    private $skipProvisionedCandidates = false;

    /** @var CandidateBuilder */
    private $candidateBuilder;

    /** @var DataRowsBuilder */
    private $dataRowsBuilder;

    /** @var integer */
    private $queuedCandidatesCount = 0;

    /** @var array */
    private $errorMessages = [];

    /** @var ProvisionDispatcher */
    private $dispatcher;

    /** @var ProvisionConfig */
    private $config;


    /**
     * @param string              $name
     * @param WorkingDirectory    $workingDirectory
     * @param Connection          $connection
     * @param CandidateBuilder    $candidateBuilder
     * @param DataRowsBuilder     $dataRowsBuilder
     * @param ProvisionDispatcher $dispatcher
     * @param ProvisionConfig     $config
     */
    public function __construct(
        $name,
        WorkingDirectory $workingDirectory,
        Connection $connection,
        CandidateBuilder $candidateBuilder,
        DataRowsBuilder $dataRowsBuilder,
        ProvisionDispatcher $dispatcher,
        ProvisionConfig $config
    ) {
        $this->workingDirectory = $workingDirectory;
        $this->connection       = $connection;
        $this->candidateBuilder = $candidateBuilder;
        $this->dataRowsBuilder  = $dataRowsBuilder;
        $this->dispatcher       = $dispatcher;
        $this->config           = $config;

        parent::__construct($name);
    }


    protected function configure()
    {
        $this
            ->setDescription('Execute the content of *.sql files from given')
            ->setHelp(self::HELP_MESSAGE);
        $this->addOption('init', null, InputOption::VALUE_NONE, 'Initialize .env in given directory');
        $this->addOption(
            'skip-provisioned',
            null,
            InputOption::VALUE_NONE,
            'Skip provisioned candidates from printing'
        );
        $this->addOption(
            'skip-syntax-check',
            null,
            InputOption::VALUE_NONE,
            'Skip executing of sql syntax check for each entry'
        );
        $this->addOption(
            'skip-email',
            null,
            InputOption::VALUE_NONE,
            'Skip email notification after provision is done'
        );
        $this->addOption(
            'force',
            'f',
            InputOption::VALUE_NONE,
            'Execute provision candidates without asking for confirmation'
        );
        $this->addOption(
            'env-file',
            null,
            InputOption::VALUE_OPTIONAL,
            'Environment variables file path. Use this env file to seed base environment variables.'
        );
        $this->addArgument('path', InputArgument::REQUIRED, 'Path to dbdeploys folder');
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        if ($envFile = $input->getOption('env-file')) {
            $this->config->withEnvPath($envFile);
        }

        if ($input->getOption('force')) {
            $this->config->force();
        }

        if ($input->getOption('skip-email')) {
            $this->config->skipEmail();
        }

        if ($input->getOption('skip-syntax-check')) {
            $this->config->skipSyntaxCheck();
        }

        if ($input->getOption('skip-provisioned')) {
            $this->config->skipProvisioned();
        }

        $this->config->load();
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->start($input, $output);
        $this->io->section('Working directory processing');
        $this->io->comment(sprintf('Using env file from [%s]', $this->config->getEnvPath()));

        if ($this->config->isSkipProvisioned()) {
            $this->skipProvisionedCandidates = true;
            $this->io->warning('Hiding of provisioned candidates ENABLED');
        }

        if ($this->config->isSkipSyntaxCheck()) {
            $this->dispatcher->skipSyntaxCheck();
        }

        $this->processWorkingDirectory($input);
        $this->processCandidates();

        return 0;
    }


    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    protected function start(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);
        $this->dispatcher->setInputOutput($this->io);

        $this->io->title('SQL Provisioner');
        $this->io->block(sprintf('Provisioning started at %s', date('Y-m-d H:i:s')));
    }


    protected function fetchCandidates()
    {
        $this->iterateOverWorkingDirectory();

        if (!empty($this->errorMessages)) {
            $this->showSyntaxErrors();
        }

        if (!$this->queuedCandidatesCount) {
            $this->io->block('All candidates scripts were executed already.');
            $this->dispatcher->finalizeAndExit();
        }
    }


    /**
     * @param SplFileInfo $candidateFile
     */
    protected function processCandidateFile($candidateFile)
    {
        $candidate = $this->candidateBuilder->build($candidateFile);
        array_push($this->workingDirectoryCandidates, $candidate);

        try {
            $this->dispatcher->validate($candidate);

            // can be also ignored but without error
            if ($candidate->isQueued()) {
                $this->queuedCandidatesCount++;
            }
        } catch (RuntimeException $validationError) {
            if ($validationError->getMessage()) {
                array_push($this->errorMessages, $validationError->getMessage());
            }
        }
    }


    protected function iterateOverWorkingDirectory()
    {
        foreach ($this->workingDirectory->getCandidates() as $candidateFile) {
            $this->processCandidateFile($candidateFile);
        }

        $this->io->text(sprintf('<info>%d</info> files found', count($this->workingDirectoryCandidates)));

        if (count($this->workingDirectoryCandidates) === 0) {
            throw Exception::noFilesInDirectory($this->workingDirectory);
        }
    }


    protected function showSyntaxErrors()
    {
        $this->io->warning(sprintf('Detected %d syntax checking issues', count($this->errorMessages)));
        $this->printAllCandidates();
        $this->io->warning(implode("\n", $this->errorMessages));
        $this->dispatcher->finalizeAndExit();
    }


    /**
     * @param InputInterface $input
     */
    protected function processWorkingDirectory(InputInterface $input)
    {
        $this->workingDirectory = $this->workingDirectory->cd($input->getArgument('path'));
        $this->loadOrCreateEnvironment($input);
        $this->io->success('DONE');
    }


    /**
     * @param InputInterface $input
     */
    private function loadOrCreateEnvironment(InputInterface $input)
    {
        if ($input->getOption('init')) {
            $this->workingDirectory->createEnvironmentFile();
            $this->io->success(sprintf('Initial .env file created in %s', $this->workingDirectory));
            die(0);
        }

        $this->workingDirectory->loadEnvironment();
    }


    private function setConnectionParameters()
    {
        $this->connection->useMysql($_ENV['DATABASE_HOST'], $_ENV['DATABASE_PORT'], $_ENV['DATABASE_NAME'],
                                    $_ENV['DATABASE_USER'], $_ENV['DATABASE_PASSWORD']);
        $this->connection->setProvisioningTable($_ENV['PROVISIONING_TABLE']);
        $this->connection->setCriteriaColumn($_ENV['PROVISIONING_TABLE_CANDIDATE_NUMBER_COLUMN']);

        $this->io->success(sprintf('Connection with `%s` established', $_ENV['DATABASE_NAME']));
    }


    private function processCandidates()
    {
        $this->io->newLine(2);
        $this->io->section('Candidates processing');

        $this->setConnectionParameters();
        $this->fetchCandidates();
        $this->printAllCandidates();
        $this->dispatcher->deploy($this->workingDirectoryCandidates, $this->queuedCandidatesCount);
    }


    private function printAllCandidates()
    {
        $this->io->table(
            DataRowsBuilder::TABLE_HEADERS,
            $this->dataRowsBuilder->build(
                $this->workingDirectoryCandidates, $this->skipProvisionedCandidates)
        );
        $this->io->newLine(3);
    }
}
