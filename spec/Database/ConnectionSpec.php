<?php

namespace spec\Tworzenieweb\SqlProvisioner\Database;

use Tworzenieweb\SqlProvisioner\Database\Connection;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * Class ConnectionSpec
 * @package spec\Tworzenieweb\SqlProvisioner\Database
 * @mixin Connection
 */
class ConnectionSpec extends ObjectBehavior
{
    const CONNECTION_PROVISIONING_TABLE = 'changelog';
    const CONNECTION_CRITERIA_COLUMN = 'changelog_number';

    function it_should_set_connection_parameters()
    {
        $this->useSqlite(true);
        $this->setProvisioningTable(self::CONNECTION_PROVISIONING_TABLE)
             ->setCriteriaColumn(self::CONNECTION_CRITERIA_COLUMN);

        $this->getCurrentConnection();
    }
}
