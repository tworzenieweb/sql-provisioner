<?php

namespace spec\Tworzenieweb\SqlProvisioner\Formatter;

use Tworzenieweb\SqlProvisioner\Formatter\Sql;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * Class SqlSpec
 * @package spec\Tworzenieweb\SqlProvisioner\Formatter
 * @mixin Sql
 */
class SqlSpec extends ObjectBehavior
{
    function it_should_format()
    {
        $this->format('select * from foo')->shouldContain('foo');
    }
}
