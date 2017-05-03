<?php

namespace Tworzenieweb\SqlProvisioner\Database;

use PDO;

/**
 * @author Luke Adamczewski
 * @package Tworzenieweb\SqlProvisioner\Database
 */
class Connection
{
    const DSN = 'mysql:host=%s;port=%d;dbname=%s';

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



    /**
     * @param string $host
     */
    public function setHost($host)
    {
        $this->host = $host;
    }



    /**
     * @param string $port
     */
    public function setPort($port)
    {
        $this->port = $port;
    }



    /**
     * @param string $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }



    /**
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }



    /**
     * @param string $databaseName
     */
    public function setDatabaseName($databaseName)
    {
        $this->databaseName = $databaseName;
    }



    /**
     * @return PDO
     */
    public function getCurrentConnection()
    {
        if (null === $this->currentConnection) {
            $this->currentConnection = new PDO(
                sprintf(self::DSN, $this->host, $this->port, $this->databaseName), $this->user, $this->password
            );
            $this->currentConnection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->currentConnection->setAttribute(PDO::ATTR_EMULATE_PREPARES, true);
            $this->currentConnection->setAttribute(PDO::MYSQL_ATTR_DIRECT_QUERY, true);
        }

        return $this->currentConnection;
    }



    /**
     * @param string $provisioningTable
     */
    public function setProvisioningTable($provisioningTable)
    {
        $this->provisioningTable = $provisioningTable;
    }



    /**
     * @param string $criteriaColumn
     */
    public function setCriteriaColumn($criteriaColumn)
    {
        $this->criteriaColumn = $criteriaColumn;
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