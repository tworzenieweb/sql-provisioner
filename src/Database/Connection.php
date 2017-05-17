<?php

namespace Tworzenieweb\SqlProvisioner\Database;

use PDO;

/**
 * @author Luke Adamczewski
 * @package Tworzenieweb\SqlProvisioner\Database
 */
class Connection
{
    /** @var string */
    private $host;

    /** @var string */
    private $port;

    /** @var string */
    private $user;

    /** @var string */
    private $password;

    /** @var string */
    private $databaseName;

    /** @var PDO */
    private $currentConnection;

    /** @var string */
    private $provisioningTable;

    /** @var string */
    private $criteriaColumn;

    private $dsn;


    /**
     * @param string $host
     * @return $this
     */
    public function setHost($host)
    {
        $this->host = $host;

        return $this;
    }



    /**
     * @param string $port
     * @return $this
     */
    public function setPort($port)
    {
        $this->port = $port;

        return $this;
    }



    /**
     * @param string $user
     * @return $this
     */
    public function setUser($user)
    {
        $this->user = $user;

        return $this;
    }



    /**
     * @param string $password
     * @return $this
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }



    /**
     * @param string $databaseName
     * @return $this
     */
    public function setDatabaseName($databaseName)
    {
        $this->databaseName = $databaseName;

        return $this;
    }



    /**
     * @param bool $useMemoryStorage
     * @return $this
     */
    public function useSqlite($useMemoryStorage = false)
    {
        $this->dsn = $useMemoryStorage ? 'sqlite::memory:' : 'sqlite:%s';

        return $this;
    }



    /**
     * @return $this
     */
    public function useMysql()
    {
        $this->dsn = 'mysql:host=%s;port=%d;dbname=%s';

        return $this;
    }

    /**
     * @return PDO
     */
    public function getCurrentConnection()
    {
        if (null === $this->currentConnection) {
            $this->currentConnection = new PDO(
                sprintf($this->dsn, $this->host, $this->port, $this->databaseName),
                $this->user,
                $this->password
            );
            $this->currentConnection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->currentConnection->setAttribute(PDO::ATTR_EMULATE_PREPARES, true);
            $this->currentConnection->setAttribute(PDO::MYSQL_ATTR_DIRECT_QUERY, true);
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
