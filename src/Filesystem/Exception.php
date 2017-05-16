<?php

namespace Tworzenieweb\SqlProvisioner\Filesystem;

/**
 * @author Luke Adamczewski
 * @package Tworzenieweb\SqlProvisioner\Filesystem
 */
class Exception extends \Exception
{
    /**
     * @param WorkingDirectory $directory
     * @return Exception
     */
    public static function noFilesInDirectory(WorkingDirectory $directory)
    {
        return new self(sprintf('No files matching search criteria were found in %s', $directory));
    }
}