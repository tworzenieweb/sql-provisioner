<?php

namespace spec\Tworzenieweb\SqlProvisioner\Filesystem;

use Prophecy\Argument;
use Symfony\Component\Filesystem\Filesystem;
use Tworzenieweb\SqlProvisioner\Filesystem\CandidatesFinder;
use Tworzenieweb\SqlProvisioner\Filesystem\EnvironmentLoaderInterface;
use Tworzenieweb\SqlProvisioner\Filesystem\WorkingDirectory;
use PhpSpec\ObjectBehavior;

/**
 * @author Luke Adamczewski
 * @package spec\Tworzenieweb\SqlProvisioner\Filesystem
 * @mixin WorkingDirectory
 */
class WorkingDirectorySpec extends ObjectBehavior
{
    private $dotEnvPath;

    private $currentDirectory;

    function let(CandidatesFinder $candidatesFinder, Filesystem $filesystem, EnvironmentLoaderInterface $environmentLoader)
    {
        $this->currentDirectory = __DIR__;
        $this->dotEnvPath = $this->currentDirectory . DIRECTORY_SEPARATOR . '.env';
        $filesystem->isAbsolutePath($this->currentDirectory)->willReturn(true);
        $this->beConstructedWith($this->currentDirectory, $candidatesFinder, $filesystem, $environmentLoader);
    }

    function it_should_provide_absolute_path()
    {
        $this->getCurrentDirectoryAbsolute()->shouldReturn($this->currentDirectory);
    }

    function it_should_return_path_for_to_string_method()
    {
        $this->__toString()->shouldReturn($this->currentDirectory);
    }

    function it_should_touch_dot_file(Filesystem $filesystem)
    {
        $filesystem->isAbsolutePath($this->getCurrentDirectoryAbsolute())->shouldBeCalled();
        $filesystem->dumpFile(Argument::type('string'), WorkingDirectory::DRAFT_CONTENT)->shouldBeCalled();
        $this->createEnvironmentFile();
    }

    /**
     * @param EnvironmentLoaderInterface $environmentLoader
     */
    function it_should_load_dot_env(EnvironmentLoaderInterface $environmentLoader)
    {
        $environmentLoader->load($this)->shouldBeCalled();
        $this->loadEnvironment();
    }

    function it_should_get_candidates(CandidatesFinder $candidatesFinder)
    {
        $candidatesFinder->find($this->currentDirectory)->shouldBeCalled();
        $this->getCandidates();
    }
}
