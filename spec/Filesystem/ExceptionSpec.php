<?php

namespace spec\Tworzenieweb\SqlProvisioner\Filesystem;

use Tworzenieweb\SqlProvisioner\Filesystem\Exception;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Tworzenieweb\SqlProvisioner\Filesystem\WorkingDirectory;

/**
 * @author Luke Adamczewski
 * @package spec\Tworzenieweb\SqlProvisioner\Filesystem
 * @mixin  Exception
 */
class ExceptionSpec extends ObjectBehavior
{
    function it_should_return_filesystem_exception(WorkingDirectory $workingDirectory)
    {
        $workingDirectory->__toString()->willReturn(__DIR__);
        $this->beConstructedThrough('noFilesInDirectory', [$workingDirectory]);
        $this->getMessage()->shouldBe(sprintf('No files matching search criteria were found in %s', __DIR__));
    }
}
