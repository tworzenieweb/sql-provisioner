<?php

namespace Tworzenieweb\SqlProvisioner\Check;

use Tworzenieweb\SqlProvisioner\Model\Candidate;

/**
 * @author Luke Adamczewski
 * @package Tworzenieweb\SqlProvisioner\Database
 */
interface Check
{
    /**
     * @param Candidate $candidate
     * @return bool True / False based on the fact if check is met or not
     */
    public function execute(Candidate $candidate);



    /**
     * @return string
     */
    public function getErrorCode();



    /**
     * @return string
     */
    public function getLastErrorMessage();
}