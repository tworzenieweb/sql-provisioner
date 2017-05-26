<?php

namespace spec\Tworzenieweb\SqlProvisioner\Model;

use Tworzenieweb\SqlProvisioner\Model\Exception;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * Class ExceptionSpec
 * @package spec\Tworzenieweb\SqlProvisioner\Model
 * @mixin Exception
 */
class ExceptionSpec extends ObjectBehavior
{
    function it_should_return_unsupported_application_state()
    {
        $this->beConstructedThrough(
            'unsupportedCandidateState',
            ['fooState', ['bar', 'baz']]
        );

        $this->shouldHaveType(Exception::class);
    }
}
