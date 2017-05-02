<?php

namespace Tworzenieweb\SqlProvisioner\Processor;

use Tworzenieweb\SqlProvisioner\Database\Check;
use Tworzenieweb\SqlProvisioner\Database\Connection;
use Tworzenieweb\SqlProvisioner\Model\Candidate;

/**
 * @author Luke Adamczewski
 * @package Tworzenieweb\SqlProvisioner\Processor
 */
class CandidateProcessor
{
    /** @var Check[] */
    private $checks;

    /** @var string */
    private $lastError;



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



    public function isValid(Candidate $candidate)
    {
        $this->lastError = null;
        $connection = $this->getConnection();
        foreach ($this->checks as $check) {
            if ($check->execute($candidate, $connection)) {
                $this->lastError = $check->getErrorCode();
                return false;
            }
        }

        return true;
    }



    /**
     * @return string
     */
    public function getLastError()
    {
        return $this->lastError;
    }



    private function getConnection()
    {
        return $this->connection->getCurrentConnection();
    }
}