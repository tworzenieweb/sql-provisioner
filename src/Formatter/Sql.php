<?php

namespace Tworzenieweb\SqlProvisioner\Formatter;

use SqlFormatter;

/**
 * @author Luke Adamczewski
 * @package Tworzenieweb\SqlProvisioner\Formatter
 */
class Sql
{
    /**
     * @param $sql
     * @return String
     */
    public function format($sql)
    {
        return SqlFormatter::format(SqlFormatter::removeComments($sql));
    }
}