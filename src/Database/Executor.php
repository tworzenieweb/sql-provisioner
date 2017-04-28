<?php

namespace Tworzenieweb\SqlProvisioner\Database;

use PDO;

/**
 * @author Luke Adamczewski
 * @package Tworzenieweb\SqlProvisioner\Database
 */
class Executor
{
    /** @var PDO */
    private $connection;

    /** @var Check[] */
    private $checks;



    /**
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
        $this->checks = [];
    }



    /**
     * @param Check $check
     */
    public function addCheck(Check $check)
    {
        array_push($this->checks, $check);
    }



    /**
     * @param $name
     * @param $content
     */
    public function execute($name, $content)
    {
        if(false === $this->performQuerySkipChecks($name)) {
            $statement = $this->getConnection()->prepare($content);
            $statement->execute();
        }
    }



    /**
     * @param $name
     * @return bool
     */
    private function performQuerySkipChecks($name)
    {
        foreach ($this->checks as $check) {
            $check->setDeployScriptName($name);
            if ($check->execute($this->getConnection())) {
                return true;
            }
        }

        return false;
    }



    /**
     * @return PDO
     */
    private function getConnection()
    {
        return $this->connection->getConnection();
    }
}