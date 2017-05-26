<?php

namespace spec\Tworzenieweb\SqlProvisioner\Database;

use Tworzenieweb\SqlProvisioner\Database\Parser;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Tworzenieweb\SqlProvisioner\Model\Candidate;

/**
 * Class ParserSpec
 * @package spec\Tworzenieweb\SqlProvisioner\Database
 * @mixin Parser
 */
class ParserSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(__DIR__);
    }


    function it_should_execute_parsing_process(Candidate $candidate)
    {
        $this->execute($candidate);
    }
}
