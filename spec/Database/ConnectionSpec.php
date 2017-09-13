<?php

namespace spec\Tworzenieweb\SqlProvisioner\Database;

use Tworzenieweb\SqlProvisioner\Database\Connection;
use PhpSpec\ObjectBehavior;
use Tworzenieweb\SqlProvisioner\Database\ConnectionFactoryInterface;

/**
 * Class ConnectionSpec
 *
 * @package spec\Tworzenieweb\SqlProvisioner\Database
 * @mixin Connection
 */
class ConnectionSpec extends ObjectBehavior
{
    const CONNECTION_PROVISIONING_TABLE = 'changelog';
    const CONNECTION_CRITERIA_COLUMN = 'changelog_number';

    function let(ConnectionFactoryInterface $connectionFactory)
    {
        $this->beConstructedWith($connectionFactory);
    }


    function it_should_use_pdo(ConnectionFactoryInterface $connectionFactory)
    {
        $connectionFactory->build('sqlite::memory:', null, null)->shouldBeCalled();
        $this->useSqlite();
        $this->setProvisioningTable(self::CONNECTION_PROVISIONING_TABLE)
             ->setCriteriaColumn(self::CONNECTION_CRITERIA_COLUMN);

        $this->getCurrentConnection();
    }



    function it_should_use_mysql(ConnectionFactoryInterface $connectionFactory)
    {
        $connectionFactory->build('mysql:host=localhost;port=3306;dbname=test;charset=utf8', 'root', 'passwd')->shouldBeCalled();
        $this->useMysql('localhost', '3306', 'test', 'root', 'passwd')->shouldReturn($this);

        $this->setProvisioningTable(self::CONNECTION_PROVISIONING_TABLE)
             ->setCriteriaColumn(self::CONNECTION_CRITERIA_COLUMN);

        $this->getCurrentConnection();
    }
}
