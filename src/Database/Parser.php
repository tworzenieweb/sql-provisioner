<?php

namespace Tworzenieweb\SqlProvisioner\Database;

use Symfony\Component\Process\InputStream;
use Symfony\Component\Process\Process;
use Tworzenieweb\SqlProvisioner\Model\Candidate;

/**
 * @author Luke Adamczewski
 * @package Tworzenieweb\SqlProvisioner\Database
 */
class Parser
{
    const PROCESS_TIMEOUT = 120;
    const PROCESS_COMMAND = 'vendor/bin/php-sqllint -r emacs -';

    /** @var string */
    private $rootPath;



    /**
     * @param string $rootPath
     */
    public function __construct($rootPath)
    {
        $this->rootPath = $rootPath;
    }



    /**
     * @param Candidate $candidate
     * @return array
     */
    public function execute(Candidate $candidate)
    {
        $process = new Process(
            self::PROCESS_COMMAND,
            realpath($this->rootPath),
            null,
            $candidate->getContent(),
            self::PROCESS_TIMEOUT
        );
        $process->run();

        // remove extra header
        $parsingResult = explode("\n", $process->getOutput());
        array_shift($parsingResult);

        return implode("\n", $parsingResult);
    }
}
