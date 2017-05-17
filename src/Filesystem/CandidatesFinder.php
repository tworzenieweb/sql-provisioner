<?php

namespace Tworzenieweb\SqlProvisioner\Filesystem;

use Symfony\Component\Finder\Finder;

/**
 * @author Luke Adamczewski
 * @package Tworzenieweb\SqlProvisioner\Filesystem
 */
class CandidatesFinder
{
    const FILES_MASK = '/^\d{3,}\_.*\.sql$/';



    /**
     * @param string $path
     * @return Finder
     */
    public function find($path)
    {
        return Finder::create()
            ->files()
            ->name(self::FILES_MASK)
            ->sortByName()
            ->in($path);
    }
}
