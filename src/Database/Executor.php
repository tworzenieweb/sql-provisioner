<?php

namespace Tworzenieweb\SqlProvisioner\Database;

use PDO;
use PDOException;
use Tworzenieweb\SqlProvisioner\Model\Candidate;

/**
 * @author Luke Adamczewski
 * @package Tworzenieweb\SqlProvisioner\Database
 */
class Executor
{
    /** @var Connection */
    private $connection;



    /**
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }



    /**
     * @param Candidate $candidate
     * @throws Exception
     */
    public function execute(Candidate $candidate)
    {
        $connection = $this->getConnection();

        try {
            $statement = $connection->prepare($candidate->getContent());
            $statement->execute();
        } catch (PDOException $pdoException) {
            $exception = Exception::candidateScriptError($candidate, $pdoException);
            throw $exception;
        }
    }



    /**
     * @return PDO
     */
    private function getConnection()
    {
        return $this->connection->getCurrentConnection();
    }
}