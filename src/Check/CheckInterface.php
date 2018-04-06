<?php

namespace Tworzenieweb\SqlProvisioner\Check;

use Tworzenieweb\SqlProvisioner\Model\Candidate;

/**
 * @author Luke Adamczewski
 * @package Tworzenieweb\SqlProvisioner\Database
 */
interface CheckInterface
{
    /**
     * @param Candidate $candidate
     * @return bool True / False based on the fact if check is met or not
     */
    public function execute(Candidate $candidate): bool;



    /**
     * @return string
     */
    public function getErrorCode(): string;



    /**
     * @return string
     */
    public function getLastErrorMessage(): string;
}
