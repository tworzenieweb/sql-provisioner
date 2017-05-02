<?php

namespace Tworzenieweb\SqlProvisioner\Database;

use PDO;
use Tworzenieweb\SqlProvisioner\Model\Candidate;

/**
 * @author Luke Adamczewski
 * @package Tworzenieweb\SqlProvisioner\Database
 */
class HasDbDeployCheck implements Check
{
    const SQL = <<<SQL
SELECT `id`
FROM `changelog_database_deployments`
WHERE `deploy_script_number` = ?
SQL;
    const ERROR_STATUS = 'ALREADY_DEPLOYED';



    /**
     * @param Candidate $candidate
     * @param PDO $connection
     * @return bool
     */
    public function execute(Candidate $candidate, PDO $connection)
    {

        $statement = $connection->prepare(self::SQL);
        $statement->execute([$candidate->getNumber()]);

        return (boolean) $statement->fetchColumn();
    }



    /**
     * @return string
     */
    public function getErrorCode()
    {
        return self::ERROR_STATUS;
    }
}