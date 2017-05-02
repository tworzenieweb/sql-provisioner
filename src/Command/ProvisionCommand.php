<?php

namespace Tworzenieweb\SqlProvisioner\Command;

use Dotenv\Dotenv;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;
use Tworzenieweb\SqlProvisioner\Database\Connection;
use Tworzenieweb\SqlProvisioner\Database\Exception as DatabaseException;
use Tworzenieweb\SqlProvisioner\Database\Executor;
use Tworzenieweb\SqlProvisioner\Database\HasDbDeployCheck;
use Tworzenieweb\SqlProvisioner\Filesystem\CandidatesFinder;
use Tworzenieweb\SqlProvisioner\Filesystem\Exception;
use Tworzenieweb\SqlProvisioner\Formatter\Sql;
use Tworzenieweb\SqlProvisioner\Model\Candidate;
use Tworzenieweb\SqlProvisioner\Model\CandidateBuilder;
use Tworzenieweb\SqlProvisioner\Processor\CandidateProcessor;

/**
 * @author Luke Adamczewski
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
</comment>

If you want to create initial .env use <info>--init</info>

<info>%command.name% --init [path-to-folder]</info>

The next step is searching for sql files and trying to queue them in numerical order.
Before the insert, it will print the formatted output of a file and result of internal syntax check.
Then you can either skip or execute each.

If you would like to skip already provisioned candidates use <info>--skip-provisioned</info>
EOF;

    const MANDATORY_ENV_VARIABLES = [
        'DATABASE_USER',
        'DATABASE_PASSWORD',
        'DATABASE_NAME',
        'DATABASE_PORT',
        'DATABASE_HOST',
    ];
    const TABLE_HEADERS = ['FILENAME', 'STATUS'];

    /** @var Candidate[] */
    private $workingDirectoryCandidates;

    /** @var Sql */
    private $sqlFormatter;

    /** @var Filesystem */
    private $filesystem;

    /** @var string */
    private $workingDirectory;

    /** @var CandidatesFinder */
    private $finder;

    /** @var SymfonyStyle */
    private $io;

    /** @var Connection */
    private $connection;

    /** @var CandidateProcessor */
    private $processor;

    /** @var Executor */
    private $executor;

    /** @var boolean */
    private $skipProvisionedCandidates;

    /** @var CandidateBuilder */
    private $builder;

    /** @var bool */
    private $hasQueuedCandidates;

    /** @var integer */
    private $queuedCandidatesCount;



    /**
     * @param string $name
     * @param Connection $connection
     * @param Sql $sqlFormatter
     * @param CandidatesFinder $finder
     * @param CandidateProcessor $processor
     * @param CandidateBuilder $builder
     * @param Executor $executor
     */
    public function __construct(
        $name,
        Connection $connection,
        Sql $sqlFormatter,
        CandidatesFinder $finder,
        CandidateProcessor $processor,
        CandidateBuilder $builder,
        Executor $executor
    ) {
        $this->connection = $connection;
        $this->sqlFormatter = $sqlFormatter;
        $this->filesystem = new Filesystem();
        $this->finder = $finder;
        $this->processor = $processor;
        $this->builder = $builder;
        $this->executor = $executor;

        $this->workingDirectoryCandidates = [];
        $this->skipProvisionedCandidates = false;
        $this->hasQueuedCandidates = false;
        $this->queuedCandidatesCount = 0;

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
        $this->addArgument('path', InputArgument::REQUIRED, 'Path to dbdeploys folder');
    }



    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->start($input, $output);
        $this->io->section('Working directory processing');

        if ($input->getOption('skip-provisioned')) {
            $this->skipProvisionedCandidates = true;
            $this->io->warning('Hiding of provisioned candidates ENABLED');
        }

        $path = $input->getArgument('path');
        $this->workingDirectory = $this->buildAbsolutePath($path);

        $this->loadDotEnv($input);
        $this->processWorkingDirectory();
        $this->finish();

        return 0;
    }



    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function start(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);
        $this->io->title('SQL Provisioner');
        $this->io->block(sprintf('Provisioning started at %s', date('Y-m-d H:i:s')));
    }



    protected function fetchCandidates()
    {
        $index = 1;
        foreach ($this->finder->find($this->workingDirectory) as $candidateFile) {
            $candidate = $this->builder->build($candidateFile);
            array_push($this->workingDirectoryCandidates, $candidate);

            if ($this->processor->isValid($candidate)) {
                $candidate->markAsQueued();
                $candidate->setIndex($index++);
                $this->hasQueuedCandidates = true;
                $this->queuedCandidatesCount++;
            } else {
                $candidate->markAsIgnored($this->processor->getLastError());
            }
        }

        $this->io->text(sprintf('<info>%d</info> files found', count($this->workingDirectoryCandidates)));

        if (count($this->workingDirectoryCandidates) === 0) {
            throw Exception::noFilesInDirectory($this->workingDirectory);
        }

        if (false === $this->hasQueuedCandidates) {
            $this->io->block('All candidates scripts were executed already.');
            $this->finish();
        }
    }



    /**
     * @param $path
     * @return string
     */
    private function buildAbsolutePath($path)
    {
        $absolutePath = $path;

        if (!$this->filesystem->isAbsolutePath($path)) {
            $absolutePath = realpath($path);
        }

        return $absolutePath;
    }



    /**
     * @param InputInterface $input
     */
    private function loadDotEnv(InputInterface $input)
    {
        if ($input->getOption('init')) {
            $this->initializeDotEnv();
            $this->io->success(sprintf('Initial .env file created in %s', $this->workingDirectory));
            die(0);
        }

        (new Dotenv($this->workingDirectory))->load();
        $this->io->success(sprintf('%s file parsed', $this->getDotEnvFilepath()));

        $this->checkAndSetConnectionParameters();
    }



    private function initializeDotEnv()
    {
        $initialDotEnvFilepath = $this->getDotEnvFilepath();
        $this->filesystem->dumpFile(
            $initialDotEnvFilepath,
            <<<DRAFT
DATABASE_USER=[user]
DATABASE_PASSWORD=[password]
DATABASE_HOST=[host]
DATABASE_PORT=[port]
DATABASE_NAME=[database]
DRAFT
        );
    }



    /**
     * @return string
     */
    private function getDotEnvFilepath()
    {
        return $this->workingDirectory . '/.env';
    }



    private function checkAndSetConnectionParameters()
    {
        $hasAllKeys = count(
                array_intersect_key(
                    array_flip(self::MANDATORY_ENV_VARIABLES),
                    $_ENV
                )
            ) === count(self::MANDATORY_ENV_VARIABLES);

        if (!$hasAllKeys) {
            throw new \LogicException('Provided .env is missing the mandatory keys');
        }

        $this->connection->setDatabaseName($_ENV['DATABASE_NAME']);
        $this->connection->setHost($_ENV['DATABASE_HOST']);
        $this->connection->setUser($_ENV['DATABASE_USER']);
        $this->connection->setPassword($_ENV['DATABASE_PASSWORD']);
        $this->connection->getCurrentConnection();

        $this->io->success(sprintf('Connection with `%s` established', $_ENV['DATABASE_NAME']));
    }



    private function processWorkingDirectory()
    {
        $this->io->newLine(2);
        $this->io->section('Candidates processing');

        $this->fetchCandidates();
        $this->printAllCandidates();
        $this->processQueuedCandidates();
    }



    /**
     * @param Candidate $candidate
     */
    private function executeCandidateScript(Candidate $candidate)
    {
        $this->io->warning(
            sprintf(
                'PROCESSING [%d/%d] %s',
                $candidate->getIndex(),
                $this->queuedCandidatesCount,
                $candidate->getName()
            )
        );
        $this->io->text($this->sqlFormatter->format($candidate->getContent()));
        $action = $this->io->choice('What action to perform', ['DEPLOY', 'SKIP', 'QUIT']);

        switch ($action) {
            case 'DEPLOY':
                $this->deployCandidate($candidate);
                break;
            case 'QUIT':
                $this->finish();
                break;
        }
    }



    private function printAllCandidates()
    {
        $self = $this;
        $rows = array_map(
            function (Candidate $candidate) use ($self) {
                $status = $candidate->getStatus();

                switch ($status) {
                    case Candidate::STATUS_QUEUED:
                        $status = sprintf('<comment>%s</comment>', $status);
                        break;
                    case HasDbDeployCheck::ERROR_STATUS:
                        if ($self->skipProvisionedCandidates) {
                            return null;
                        }
                }

                return [$candidate->getName(), $status];
            },
            $this->workingDirectoryCandidates
        );

        $this->io->table(
            self::TABLE_HEADERS,
            array_filter($rows)
        );
        $this->io->newLine(3);
    }



    private function processQueuedCandidates()
    {
        while (!empty($this->workingDirectoryCandidates)) {
            $candidate = array_shift($this->workingDirectoryCandidates);

            if ($candidate->isQueued()) {
                $this->executeCandidateScript($candidate);
            }
        }
        $this->io->writeln('<info>All candidates scripts were executed</info>');
    }



    /**
     * @param Candidate $candidate
     */
    private function deployCandidate(Candidate $candidate)
    {
        try {
            $this->executor->execute($candidate);
        } catch (DatabaseException $databaseException) {
            $this->io->error($databaseException->getMessage());
            $this->io->writeln(
                sprintf(
                    "<bg=yellow>%s\n\r%s</>",
                    $databaseException->getPrevious()->getMessage(),
                    $candidate->getContent()
                )
            );
            $this->terminate();
        }
    }



    private function finish()
    {
        $this->io->text(sprintf('Provisioning ended at %s', date('Y-m-d H:i:s')));
        die(0);
    }



    private function terminate()
    {
        $this->io->text(sprintf('Provisioning ended with error at %s', date('Y-m-d H:i:s')));
        die(1);
    }
}