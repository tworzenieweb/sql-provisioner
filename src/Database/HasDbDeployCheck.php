<?php

namespace Tworzenieweb\SqlProvisioner\Database;

use PDO;
use RuntimeException;

/**
 * @author Luke Adamczewski
 * @package Tworzenieweb\SqlProvisioner\Database
 */
class HasDbDeployCheck implements Check
{
    const SQL = <<<SQL
SELECT `id`
FROM `changelog_database_deployments`
WHERE `deploy_script_name` = ?
SQL;

    /** @var string */
    private $deployScriptName;



    /**
     * @param string $deployScriptName
     */
    public function setDeployScriptName($deployScriptName)
    {
        $this->deployScriptName = $deployScriptName;
    }



    /**
     * @param PDO $connection
     * @return bool
     */
    public function execute(PDO $connection)
    {
        if (null === $this->deployScriptName) {
            throw new RuntimeException('Deploy script name needs to be provided');
        }
        $statement = $connection->prepare(self::SQL);
        $statement->execute([$this->deployScriptName]);
        $this->deployScriptName = null;

        return (boolean) $statement->fetchColumn();
    }
}