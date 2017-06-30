<?php

namespace Tworzenieweb\SqlProvisioner\Database;

use PDO;

/**
 * @author Luke Adamczewski
 * @package Tworzenieweb\SqlProvisioner\Database
 */
interface ConnectionFactoryInterface
{
    /**
     * @param string $dsn
     * @param string $user
     * @param string $password
     * @return PDO
     */
    public function build($dsn, $user, $password);
}
