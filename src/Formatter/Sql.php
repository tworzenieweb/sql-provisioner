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
     * @param string $sql
     * @return String
     */
    public function format($sql)
    {
        $output = SqlFormatter::format(SqlFormatter::removeComments($sql));

        return preg_replace(
            "/(\x1b\[37mINSERT.*?INTO)/im",
            "\n\n$1",
            $output
        );
    }
}
