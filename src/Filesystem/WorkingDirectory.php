<?php

namespace Tworzenieweb\SqlProvisioner\Filesystem;

use Dotenv\Dotenv;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

/**
 * @author Luke Adamczewski
 * @package Tworzenieweb\SqlProvisioner\Filesystem
 */
class WorkingDirectory
{
    const MANDATORY_ENV_VARIABLES = [
        'DATABASE_USER',
        'DATABASE_PASSWORD',
        'DATABASE_NAME',
        'DATABASE_PORT',
        'DATABASE_HOST',
        'PROVISIONING_TABLE',
        'PROVISIONING_TABLE_CANDIDATE_NUMBER_COLUMN',
    ];

    /** @var string */
    private $currentDirectory;

    /** @var Filesystem */
    private $filesystem;

    /** @var string */
    private $currentDirectoryAbsolute;



    /**
     * @param $currentDirectory
     * @param CandidatesFinder $finder
     */
    public function __construct($currentDirectory, CandidatesFinder $finder)
    {
        $this->currentDirectory = $currentDirectory;
        $this->filesystem = new Filesystem();
        $this->currentDirectoryAbsolute = $this->buildAbsolutePath($currentDirectory);
        $this->finder = $finder;
    }



    /**
     * @param $newPath
     * @return WorkingDirectory
     */
    public function cd($newPath)
    {
        return new WorkingDirectory($newPath, $this->finder);
    }



    public function touchDotEnv()
    {
        $this->filesystem->dumpFile(
            $this->getDotEnvFilepath(),
            <<<DRAFT
DATABASE_USER=[user]
DATABASE_PASSWORD=[password]
DATABASE_HOST=[host]
DATABASE_PORT=[port]
DATABASE_NAME=[database]
PROVISIONING_TABLE=changelog_database_deployments
PROVISIONING_TABLE_CANDIDATE_NUMBER_COLUMN=deploy_script_number
DRAFT
        );
    }



    /**
     * @return string
     */
    public function getDotEnvFilepath()
    {
        return $this->currentDirectoryAbsolute . '/.env';
    }



    public function loadDotEnv()
    {
        (new Dotenv($this->currentDirectoryAbsolute))->load();

        $hasAllKeys = count(
                array_intersect_key(
                    array_flip(self::MANDATORY_ENV_VARIABLES),
                    $_ENV
                )
            ) === count(self::MANDATORY_ENV_VARIABLES);

        if (!$hasAllKeys) {
            throw new \LogicException('Provided .env is missing the mandatory keys');
        }
    }



    /**
     * @return Finder
     */
    public function getCandidates()
    {
        return $this->finder->find($this->currentDirectoryAbsolute);
    }



    /**
     * @param string $path
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
}