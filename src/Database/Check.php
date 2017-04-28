<?php

namespace Tworzenieweb\SqlProvisioner\Database;

use PDO;

/**
 * @author Luke Adamczewski
 * @package Tworzenieweb\SqlProvisioner\Database
 */
interface Check
{
    /**
     * @param PDO $connection
     * @return bool True / False based on the fact if check is met or not
     */
    public function execute(PDO $connection);



    /**
     * @param string $deployScriptName
     */
    public function setDeployScriptName($deployScriptName);
}