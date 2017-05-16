<?php

namespace Tworzenieweb\SqlProvisioner\Check;

use PDO;
use Tworzenieweb\SqlProvisioner\Database\Connection;
use Tworzenieweb\SqlProvisioner\Model\Candidate;

/**
 * @author Luke Adamczewski
 * @package Tworzenieweb\SqlProvisioner\Database
 */
class HasDbDeployCheckInterface implements CheckInterface
{
    const SQL = <<<SQL
SELECT `id`
FROM `%s`
WHERE `%s` = ?
SQL;
    const ERROR_STATUS = 'ALREADY_DEPLOYED';

    /** @var Connection */
    private $connection;



    /**
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }



    /**
     * @param Candidate $candidate
     * @return bool
     */
    public function execute(Candidate $candidate)
    {
        $statement = $this->getConnection()->prepare($this->getSqlStatement());
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



    /**
     * @return PDO
     */
    private function getConnection()
    {
        return $this->connection->getCurrentConnection();
    }



    /**
     * @return null
     */
    public function getLastErrorMessage()
    {
        return null;
    }



    /**
     * @return string
     */
    private function getSqlStatement()
    {
        return sprintf(self::SQL, $this->connection->getProvisioningTable(), $this->connection->getCriteriaColumn());
    }
}