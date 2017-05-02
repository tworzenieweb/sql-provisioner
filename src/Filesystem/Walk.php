<?php

namespace Tworzenieweb\SqlProvisioner\Filesystem;

use Symfony\Component\Finder\Finder;

/**
 * @author Luke Adamczewski
 * @package Tworzenieweb\SqlProvisioner\Filesystem
 */
class Walk
{

    /**
     * @param $path
     * @return Finder
     */
    public function getSqlFilesList($path)
    {
        return Finder::create()->depth(1)->files()->name('*.sql')->sortByName()->in($path);
    }
}