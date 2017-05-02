<?php

namespace Tworzenieweb\SqlProvisioner\Database;

use PDO;
use Tworzenieweb\SqlProvisioner\Model\Candidate;

/**
 * @author Luke Adamczewski
 * @package Tworzenieweb\SqlProvisioner\Database
 */
class Executor
{
    /** @var PDO */
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
        } catch (\PDOException $dberror) {
            $exception = Exception::candidateScriptError($candidate, $dberror);
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