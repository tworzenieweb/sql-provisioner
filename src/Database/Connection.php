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
    private $connection;



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
    public function getConnection()
    {
        if (null === $this->connection) {
            $this->connection = new PDO(
                sprintf(self::DSN, $this->host, $this->port, $this->databaseName), $this->user, $this->password
            );
        }

        return $this->connection;
    }
}