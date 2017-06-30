<?php

namespace Tworzenieweb\SqlProvisioner\Database;

use PDO;

/**
 * @author Luke Adamczewski
 * @package Tworzenieweb\SqlProvisioner\Database
 */
class PDOConnectionFactory implements ConnectionFactoryInterface
{
    /**
     * @inheritdoc
     */
    public function build($dsn, $user, $password)
    {
        $pdo = new PDO($dsn, $user, $password);

        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, true);
        $pdo->setAttribute(PDO::MYSQL_ATTR_DIRECT_QUERY, true);

        return $pdo;
    }
}
