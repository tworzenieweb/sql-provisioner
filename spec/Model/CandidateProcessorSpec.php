<?php

namespace spec\Tworzenieweb\SqlProvisioner\Model;

use Tworzenieweb\SqlProvisioner\Model\CandidateProcessor;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class CandidateProcessorSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(CandidateProcessor::class);
    }
}
