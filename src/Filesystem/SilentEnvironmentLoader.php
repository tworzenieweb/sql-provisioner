<?php

namespace Tworzenieweb\SqlProvisioner\Filesystem;

use Dotenv\Dotenv;
use Dotenv\Exception\InvalidPathException;

/**
 * Class SilentEnvironmentLoader
 *
 * @package Tworzenieweb\SqlProvisioner\Filesystem
 */
class SilentEnvironmentLoader implements EnvironmentLoaderInterface
{
    /**
     * @param WorkingDirectory $currentDirectory
     */
    public function load(WorkingDirectory $currentDirectory)
    {
        $loader = new Dotenv($currentDirectory->getCurrentDirectoryAbsolute());

        try {
            $loader->load();
        } catch (InvalidPathException $e) {
            // assuming .env file is missing here and then just proceed to check global env variables
        }

        $loader->required(EnvironmentLoader::MANDATORY_ENV_VARIABLES)
               ->notEmpty();
    }
}
