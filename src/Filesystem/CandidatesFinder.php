<?php

namespace Tworzenieweb\SqlProvisioner\Filesystem;

use Symfony\Component\Finder\Finder;

/**
 * @author Luke Adamczewski
 * @package Tworzenieweb\SqlProvisioner\Filesystem
 */
class CandidatesFinder
{

    /**
     * @param string $path
     * @return Finder
     */
    public function find($path)
    {
        return Finder::create()
            ->files()
            ->name('*.sql')
            ->sortByName()
            ->in($path);
    }
}