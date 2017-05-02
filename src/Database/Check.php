<?php

namespace Tworzenieweb\SqlProvisioner\Database;

use PDO;
use Tworzenieweb\SqlProvisioner\Model\Candidate;

/**
 * @author Luke Adamczewski
 * @package Tworzenieweb\SqlProvisioner\Database
 */
interface Check
{
    /**
     * @param Candidate $candidate
     * @param PDO $connection
     * @return bool True / False based on the fact if check is met or not
     */
    public function execute(Candidate $candidate, PDO $connection);



    /**
     * @return string
     */
    public function getErrorCode();
}