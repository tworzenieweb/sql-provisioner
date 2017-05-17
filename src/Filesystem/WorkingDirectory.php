<?php

namespace Tworzenieweb\SqlProvisioner\Filesystem;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

/**
 * @author Luke Adamczewski
 * @package Tworzenieweb\SqlProvisioner\Filesystem
 */
class WorkingDirectory
{
    const DRAFT_CONTENT = <<<DRAFT
DATABASE_USER=[user]
DATABASE_PASSWORD=[password]
DATABASE_HOST=[host]
DATABASE_PORT=[port]
DATABASE_NAME=[database]
PROVISIONING_TABLE=changelog_database_deployments
PROVISIONING_TABLE_CANDIDATE_NUMBER_COLUMN=deploy_script_number
DRAFT;
    const FILE_SUFFIX = '.env';

    /** @var Filesystem */
    private $filesystem;

    /** @var string */
    private $currentDirectoryAbsolute;

    /** @var EnvironmentLoaderInterface */
    private $environmentLoader;

    /** @var CandidatesFinder */
    private $finder;


    /**
     * @param string                     $currentDirectory
     * @param CandidatesFinder           $finder
     * @param Filesystem                 $filesystem
     * @param EnvironmentLoaderInterface $environmentLoader
     */
    public function __construct($currentDirectory, CandidatesFinder $finder, Filesystem $filesystem, EnvironmentLoaderInterface $environmentLoader)
    {
        $this->filesystem = $filesystem;
        $this->currentDirectoryAbsolute = $this->buildAbsolutePath($currentDirectory);
        $this->finder = $finder;
        $this->environmentLoader = $environmentLoader;
    }

    /**
     * @param string $newPath
     * @return WorkingDirectory
     */
    public function cd($newPath)
    {
        return new WorkingDirectory($newPath, $this->finder, $this->filesystem, $this->environmentLoader);
    }

    /**
     * @return void
     */
    public function createEnvironmentFile()
    {
        $targetFilename = $this->currentDirectoryAbsolute . DIRECTORY_SEPARATOR . self::FILE_SUFFIX;
        $this->filesystem->dumpFile(
            $targetFilename,
            self::DRAFT_CONTENT
        );
    }

    /**
     * @return void
     */
    public function loadEnvironment()
    {
        $this->environmentLoader->load($this);
    }

    /**
     * @return Finder
     */
    public function getCandidates()
    {
        return $this->finder->find($this->currentDirectoryAbsolute);
    }

    /**
     * @return string
     */
    public function getCurrentDirectoryAbsolute()
    {
        return $this->currentDirectoryAbsolute;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->currentDirectoryAbsolute;
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
