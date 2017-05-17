<?php

namespace Tworzenieweb\SqlProvisioner\Filesystem;

use Dotenv\Dotenv;

/**
 * Class EnvironmentLoader
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

    /**
     * @param WorkingDirectory $currentDirectory
     */
    public function load(WorkingDirectory $currentDirectory)
    {
        $loader = new Dotenv($currentDirectory->getCurrentDirectoryAbsolute());
        $loader->load();
        $loader->required(self::MANDATORY_ENV_VARIABLES)
                ->notEmpty();
    }
}
