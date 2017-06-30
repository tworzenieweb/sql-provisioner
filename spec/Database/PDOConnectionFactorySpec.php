<?php

namespace spec\Tworzenieweb\SqlProvisioner\Database;

use PDO;
use Tworzenieweb\SqlProvisioner\Database\PDOConnectionFactory;
use PhpSpec\ObjectBehavior;

/**
 * Class PDOConnectionFactorySpec
 *
 * @package spec\Tworzenieweb\SqlProvisioner\Database
 * @mixin PDOConnectionFactory
 */
class PDOConnectionFactorySpec extends ObjectBehavior
{
    function it_should_build_connection()
    {
        $this->build('sqlite::memory:', null, null)
             ->shouldReturnAnInstanceOf(PDO::class);
    }
}
