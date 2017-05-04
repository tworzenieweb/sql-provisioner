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
    /** @var string */
    private $composerBinPath;



    /**
     * @param string $composerBinPath
     */
    public function __construct($composerBinPath)
    {
        $this->composerBinPath = $composerBinPath;
    }



    /**
     * @param Candidate $candidate
     * @return array
     */
    public function execute(Candidate $candidate)
    {
        $input = new InputStream();
        $input->write($candidate->getContent());

        $process = new Process($this->getCommandString());
        $process->setInput($input);
        $process->setTimeout(null);
        $process->start();

        $input->close();
        $process->wait();

        // remove extra header
        $parsingResult = explode("\n", $process->getOutput());
        array_shift($parsingResult);

        return implode("\n", $parsingResult);
    }



    /**
     * @return string
     */
    private function getCommandString()
    {
        return sprintf('%s/php-sqllint -', realpath($this->composerBinPath));
    }
}