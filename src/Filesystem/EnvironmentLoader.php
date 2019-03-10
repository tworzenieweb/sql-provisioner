<?php

namespace Tworzenieweb\SqlProvisioner\Filesystem;

use Dotenv\Dotenv;
use Dotenv\Exception\InvalidPathException;

/**
 * Class EnvironmentLoader
 *
 * @package Tworzenieweb\SqlProvisioner\Filesystem
 */
class EnvironmentLoader implements EnvironmentLoaderInterface
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

    /** @var bool */
    protected $skipMissing = false;

    /**
     * Do not rise an exception if .env file is missing on provided path
     *
     * @param bool $skipMissing
     */
    public function skipMissing(bool $skipMissing = true)
    {
        $this->skipMissing = $skipMissing;
    }

    /**
     * @param WorkingDirectory $currentDirectory
     */
    public function load(WorkingDirectory $currentDirectory)
    {
        $loader = new Dotenv($currentDirectory->getCurrentDirectoryAbsolute());

        try {
            $loader->load();
        } catch (InvalidPathException $e) {
            if ($this->skipMissing === false) {
                throw $e;
            }

            // assuming .env file is missing here and then just proceed to check global env variables
        }

        $loader->required(self::MANDATORY_ENV_VARIABLES)
               ->notEmpty();
    }
}
