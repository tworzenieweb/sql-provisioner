<?php

namespace Tworzenieweb\SqlProvisioner\Database;

use Tworzenieweb\SqlProvisioner\Model\Candidate;

/**
 * @author Luke Adamczewski
 * @package Tworzenieweb\SqlProvisioner\Database
 */
class Exception extends \Exception
{

    /**
     * @param Candidate     $candidate
     * @param \PDOException $pdoException
     * @return Exception
     */
    public static function candidateScriptError(Candidate $candidate, \PDOException $pdoException)
    {
        $exception = new self(sprintf('There was an error during execution of %s.', $candidate->getName()), 0, $pdoException);

        return $exception;
    }
}