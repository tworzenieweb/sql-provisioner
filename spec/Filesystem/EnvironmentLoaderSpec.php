<?php

namespace spec\Tworzenieweb\SqlProvisioner\Filesystem;

use Tworzenieweb\SqlProvisioner\Filesystem\EnvironmentLoader;
use PhpSpec\ObjectBehavior;
use Tworzenieweb\SqlProvisioner\Filesystem\WorkingDirectory;

/**
 * Class EnvironmentLoaderSpec
 * @package spec\Tworzenieweb\SqlProvisioner\Filesystem
 * @mixin EnvironmentLoader
 */
class EnvironmentLoaderSpec extends ObjectBehavior
{
    function it_should_load_from_dot_env(WorkingDirectory $workingDirectory)
    {
        $workingDirectory->getCurrentDirectoryAbsolute()->willReturn(__DIR__ . DIRECTORY_SEPARATOR . 'fixture');
        $this->load($workingDirectory);
        expect(getenv('DATABASE_USER'))->shouldBe('[user]');
        expect(getenv('DATABASE_PASSWORD'))->shouldBe('[password]');
        expect(getenv('DATABASE_PORT'))->shouldBe('[port]');
        expect(getenv('DATABASE_NAME'))->shouldBe('[database]');
        expect(getenv('PROVISIONING_TABLE'))->shouldBe('changelog_database_deployments');
        expect(getenv('PROVISIONING_TABLE_CANDIDATE_NUMBER_COLUMN'))->shouldBe('deploy_script_number');
    }
}
