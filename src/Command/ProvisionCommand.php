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
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Tworzenieweb\SqlProvisioner\Database\Connection;
use Tworzenieweb\SqlProvisioner\Filesystem\Walk;
use Tworzenieweb\SqlProvisioner\Formatter\Sql;

/**
 * @author Luke Adamczewski
 * @package Tworzenieweb\SqlProvisioner\Command
 */
class ProvisionCommand extends Command
{
    const MANDATORY_ENV_VARIABLES = [
        'DATABASE_USER',
        'DATABASE_PASSWORD',
        'DATABASE_NAME',
        'DATABASE_PORT',
        'DATABASE_HOST',
    ];

    /** @var Sql */
    private $sqlFormatter;

    /** @var Filesystem */
    private $filesystem;

    /** @var string */
    private $workingDirectory;

    /** @var Walk */
    private $filesystemWalker;

    /** @var string[] */
    private $processedFiles;

    /** @var SymfonyStyle */
    private $io;

    /** @var SplFileInfo[] */
    private $queuedSqlFiles;

    /** @var Connection */
    private $connection;



    /**
     * @param null|string $name
     * @param Connection $connection
     * @param Sql $sqlFormatter
     * @param Walk $filesystemWalker
     */
    public function __construct($name, Connection $connection, Sql $sqlFormatter, Walk $filesystemWalker)
    {
        $this->connection = $connection;
        $this->sqlFormatter = $sqlFormatter;
        $this->filesystem = new Filesystem();
        $this->filesystemWalker = $filesystemWalker;
        $this->processedFiles = [];
        $this->queuedSqlFiles = [];

        parent::__construct($name);
    }



    protected function configure()
    {
        $this
        ->setDescription('Execute the content of *.sql files from given')
        ->setHelp(<<<'EOF'
The <info>%command.name% [path-to-folder]</info> command will scan the content of [path-to-folder] directory.
 
The script will look for <info>.env</info> file containing connection information in format:
<comment>
DATABASE_USER=[user]
DATABASE_PASSWORD=[password]
DATABASE_HOST=[host]
DATABASE_PORT=[port]
DATABASE_NAME=[database]
</comment>

If you want to create initial .env use --init or -i option

<info>%command.name% --init [path-to-folder]</info>

The next step is searching for sql files and trying to queue them in alphabetical order.

Before the insert, it will print the formatted output of a file and result of internal syntax check.
Then you can either skip or execute each.
After it is successfully executed it will store it in <info>.provision</info> metafile in [path-to-folder] directory. Next time this file will not be used for processing.
EOF
        );
        $this->addOption('init', null, InputOption::VALUE_NONE, 'Initialize .env in given directory');
        $this->addArgument('path', InputArgument::REQUIRED, 'Path to dbdeploys folder');
    }



    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);
        $output->writeln(sprintf('<bg=blue>Provisioning started at %s</>', date('Y-m-d H:i:s')));
        $this->io->section('Working directory processing');
        $path = $input->getArgument('path');
        $this->workingDirectory = $this->buildAbsolutePath($path);

        $this->loadDotEnv($input, $output);
        $this->loadOrCreateMetaFile();
        $this->processDbDeploys();
    }



    /**
     * @param Finder $files
     * @return array
     */
    protected function preCheckSqlFiles(Finder $files)
    {
        $data = [];
        foreach ($files as $file) {
            $currentSqlFile = $file->getFilename();
            if (!in_array($currentSqlFile, $this->processedFiles)) {
                array_push($data, [$currentSqlFile, '<comment>QUEUED</comment>']);
                array_push($this->queuedSqlFiles, $file);
            } else {
                array_push($data, [$currentSqlFile, 'IGNORED']);
            }
        }

        return $data;
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
     * @param OutputInterface $output
     */
    private function loadDotEnv(InputInterface $input, OutputInterface $output)
    {
        if ($input->getOption('init')) {
            $this->initializeDotEnv();
            $this->io->success(sprintf('Initial .env file created in %s', $this->workingDirectory));
            exit;
        }

        (new Dotenv($this->workingDirectory))->load();
        $this->io->success(sprintf('%s file parsed', $this->getDotEnvFilepath()));

        $this->checkAndSetConnectionParameters();
    }



    private function initializeDotEnv()
    {
        $initialDotEnvFilepath = $this->getDotEnvFilepath();
        $this->filesystem->dumpFile($initialDotEnvFilepath, <<<DRAFT
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

    /**
     * @return string
     */
    private function getMetaFilepath()
    {
        return $this->workingDirectory . '/.provision';
    }



    private function loadOrCreateMetaFile()
    {
        $metaFilepath = $this->getMetaFilepath();

        if ($this->filesystem->exists($metaFilepath)) {
            $this->processedFiles = file($metaFilepath);
            $this->processedFiles = array_map(function ($filename) {
                return trim($filename);
            }, $this->processedFiles);
        } else {
            $this->filesystem->touch($metaFilepath);
        }
    }



    private function processDbDeploys()
    {
        $this->io->newLine();
        $files = $this->filesystemWalker->getSqlFilesList($this->workingDirectory);
        $this->io->newLine();
        $this->io->section('Dbdeploys processing');
        $this->io->writeln(sprintf('<info>%d</info> files found', $files->count()));

        $data = $this->preCheckSqlFiles($files);

        $this->io->table(
            ['FILENAME', 'STATUS'],
            $data
        );
        $this->io->newLine(3);
        $totalFiles = count($this->queuedSqlFiles);

        foreach ($this->queuedSqlFiles as $index => $file) {
            $this->processFile($file, $index, $totalFiles);
        }
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
        $this->connection->getConnection();

        $this->io->success(sprintf('Connection with `%s` established', $_ENV['DATABASE_NAME']));
    }



    /**
     * @param $file
     * @param $index
     * @param $totalFiles
     */
    private function processFile($file, $index, $totalFiles)
    {
        list($content) = explode('--//@UNDO', $file->getContents());

        $this->io->warning(sprintf('PROCESSING [%d/%d] %s', $index + 1, $totalFiles, $file->getFilename()));
        $this->io->text($this->sqlFormatter->format($content));
        $this->io->choice('What action to perform', array('DEPLOY', 'SKIP', 'QUIT'));
    }
}