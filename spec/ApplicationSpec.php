<?php

namespace spec\Tworzenieweb\SqlProvisioner;

use Tworzenieweb\SqlProvisioner\Application;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ApplicationSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(Application::class);
    }
}
