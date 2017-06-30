<?php

namespace Tworzenieweb\SqlProvisioner\Processor;

use RuntimeException;
use Tworzenieweb\SqlProvisioner\Check\CheckInterface;
use Tworzenieweb\SqlProvisioner\Model\Candidate;

/**
 * @author Luke Adamczewski
 * @package Tworzenieweb\SqlProvisioner\Processor
 */
class CandidateProcessor
{
    const FATAL_POST_CHECK_ERROR = 'Your candidate query has failed. There was no entry in target changelog table added. Try adding query manually for more error.';
    /** @var CheckInterface[] */
    private $checks;

    /** @var string */
    private $lastError;

    /** @var string */
    private $lastErrorMessage;

    /** @var CheckInterface[] */
    private $postChecks;



    /**
     * CandidateProcessor constructor
     */
    public function __construct()
    {
        $this->postChecks = [];
        $this->checks = [];
    }



    /**
     * @param CheckInterface $check
     */
    public function addCheck(CheckInterface $check)
    {
        array_push($this->checks, $check);
    }



    public function addPostCheck(CheckInterface $check)
    {
        array_push($this->postChecks, $check);
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
     * @param Candidate $candidate
     */
    public function postValidate(Candidate $candidate)
    {
        foreach ($this->postChecks as $check) {
            if (!$check->execute($candidate)) {
                throw new RuntimeException(sprintf(self::FATAL_POST_CHECK_ERROR));
            }
        }
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



    /**
     * @param CheckInterface $check
     */
    public function removeCheck(CheckInterface $check)
    {
        $this->checks = array_filter($this->checks, function($currentCheck) use ($check) {
            return $check !== $currentCheck;
        });
    }
}
