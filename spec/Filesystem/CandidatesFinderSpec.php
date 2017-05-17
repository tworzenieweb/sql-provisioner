<?php

namespace spec\Tworzenieweb\SqlProvisioner\Filesystem;

use Symfony\Component\Finder\SplFileInfo;
use Tworzenieweb\SqlProvisioner\Filesystem\CandidatesFinder;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * Class CandidatesFinderSpec
 * @package spec\Tworzenieweb\SqlProvisioner\Filesystem
 * @mixin CandidatesFinder
 */
class CandidatesFinderSpec extends ObjectBehavior
{
    function it_should_find_candidates_in_numerical_order()
    {
        $results = $this->find(__DIR__ . DIRECTORY_SEPARATOR . 'fixture/finder');
        $results->count()->shouldReturn(2);
        $filenames = [];

        foreach ($results->getWrappedObject() as $file) {
            array_push($filenames, $file->getFilename());
        }

        expect($filenames)->shouldEqual(['001_test.sql', '002_test.sql']);
    }
}
