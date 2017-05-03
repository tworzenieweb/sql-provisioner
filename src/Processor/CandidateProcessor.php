<?php

namespace Tworzenieweb\SqlProvisioner\Processor;

use Tworzenieweb\SqlProvisioner\Check\Check;
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

    /** @var string */
    private $lastErrorMessage;



    public function __construct()
    {
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
     * @param Candidate $candidate
     * @return bool
     */
    public function isValid(Candidate $candidate)
    {
        $this->lastError = null;
        foreach ($this->checks as $check) {
            if ($check->execute($candidate)) {
                $this->lastError = $check->getErrorCode();
                $this->lastErrorMessage = $check->getLastErrorMessage();

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



    /**
     * @return string
     */
    public function getLastErrorMessage()
    {
        return $this->lastErrorMessage;
    }
}