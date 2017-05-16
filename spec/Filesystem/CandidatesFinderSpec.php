<?php

namespace spec\Tworzenieweb\SqlProvisioner\Filesystem;

use Tworzenieweb\SqlProvisioner\Filesystem\CandidatesFinder;
use PhpSpec\ObjectBehavior;

/**
 * Class CandidatesFinderSpec
 * @package spec\Tworzenieweb\SqlProvisioner\Filesystem
 * @mixin CandidatesFinder
 */
class CandidatesFinderSpec extends ObjectBehavior
{
    function it_should_find_candidates()
    {
        $results = $this->find(__DIR__ . DIRECTORY_SEPARATOR . 'fixture/finder');
        $results->count()->shouldReturn(1);
    }
}
