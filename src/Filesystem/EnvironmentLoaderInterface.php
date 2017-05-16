<?php

namespace Tworzenieweb\SqlProvisioner\Filesystem;

/**
 * Interface EnvironmentLoaderInterface
 * @package Tworzenieweb\SqlProvisioner\Filesystem
 */
interface EnvironmentLoaderInterface
{
    /**
     * @param WorkingDirectory $currentDirectory
     * @return void
     */
    public function load(WorkingDirectory $currentDirectory);
}
