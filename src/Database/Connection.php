<?php

namespace Tworzenieweb\SqlProvisioner\Database;

use PDO;

/**
 * @author Luke Adamczewski
 * @package Tworzenieweb\SqlProvisioner\Database
 */
class Connection
{
    const DSN_MYSQL = 'mysql:host=%s;port=%d;dbname=%s;charset=utf8';
    const DSN_SQLITE = 'sqlite:%s';

    /** @var string */
    private $user;

    /** @var string */
    private $password;

    /** @var PDO */
    private $currentConnection;

    /** @var string */
    private $provisioningTable;

    /** @var string */
    private $criteriaColumn;

    /** @var string */
    private $dsn;

    /** @var ConnectionFactoryInterface */
    private $connectionFactory;



    /**
     * @param ConnectionFactoryInterface $connectionFactory
     */
    public function __construct(ConnectionFactoryInterface $connectionFactory)
    {
        $this->connectionFactory = $connectionFactory;
    }



    /**
     * @param string $databaseName
     * @return $this
     */
    public function useSqlite($databaseName = ':memory:')
    {
        $this->dsn = sprintf(self::DSN_SQLITE, $databaseName);

        return $this;
    }



    /**
     * @param string $host
     * @param integer $port
     * @param string $databaseName
     * @param string $databaseUser
     * @param string $databasePassword
     * @return $this
     */
    public function useMysql($host, $port, $databaseName, $databaseUser, $databasePassword)
    {
        $this->dsn = sprintf(self::DSN_MYSQL, $host, $port, $databaseName);
        $this->user = $databaseUser;
        $this->password = $databasePassword;

        return $this;
    }

    /**
     * @return PDO
     */
    public function getCurrentConnection()
    {
        if (null === $this->currentConnection) {
            $this->currentConnection = $this->connectionFactory->build($this->dsn, $this->user, $this->password);
        }

        return $this->currentConnection;
    }



    /**
     * @param string $provisioningTable
     * @return $this
     */
    public function setProvisioningTable($provisioningTable)
    {
        $this->provisioningTable = $provisioningTable;

        return $this;
    }



    /**
     * @param string $criteriaColumn
     * @return $this
     */
    public function setCriteriaColumn($criteriaColumn)
    {
        $this->criteriaColumn = $criteriaColumn;

        return $this;
    }



    /**
     * @return string
     */
    public function getProvisioningTable()
    {
        return $this->provisioningTable;
    }



    /**
     * @return string
     */
    public function getCriteriaColumn()
    {
        return $this->criteriaColumn;
    }
}
