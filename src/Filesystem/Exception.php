<?php

namespace Tworzenieweb\SqlProvisioner\Filesystem;

/**
 * @author Luke Adamczewski
 * @package Tworzenieweb\SqlProvisioner\Filesystem
 */
class Exception extends \Exception
{
    /**
     * @param $directory
     * @return Exception
     */
    public static function noFilesInDirectory($directory)
    {
        return new self(sprintf('No files matching search criteria were found in %s', $directory));
    }
}