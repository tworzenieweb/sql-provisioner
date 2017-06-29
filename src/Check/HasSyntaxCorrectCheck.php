<?php

namespace Tworzenieweb\SqlProvisioner\Check;

use Symfony\Component\Process\Exception\ProcessTimedOutException;
use Tworzenieweb\SqlProvisioner\Database\Parser;
use Tworzenieweb\SqlProvisioner\Model\Candidate;

/**
 * @author Luke Adamczewski
 * @package Tworzenieweb\SqlProvisioner\Check
 */
class HasSyntaxCorrectCheck implements CheckInterface
{
    const ERROR_STATUS = 'HAS_SYNTAX_ERROR';

    /** @var Parser */
    private $parser;

    /** @var string */
    private $lastError;



    /**
     * @param Parser $parser
     */
    public function __construct(Parser $parser)
    {
        $this->parser = $parser;
    }



    /**
     * @param Candidate $candidate
     * @return bool True / False based on the fact if check is met or not
     */
    public function execute(Candidate $candidate)
    {
        $this->lastError = null;

        try {
            $parsingResult = $this->parser->execute($candidate);

            if (!empty($parsingResult)) {
                $this->lastError = sprintf("Syntax error during processing of %s:\n%s", $candidate->getName(), $parsingResult);

                return true;
            }
        } catch (ProcessTimedOutException $processTimedOutException) {
            $this->lastError = $processTimedOutException->getMessage() . "\nHint: you can disable syntax checking for queries too big for processing";
        }

        return false;
    }

    /**
     * @return string
     */
    public function getLastErrorMessage()
    {
        return $this->lastError;
    }


    /**
     * @return string
     */
    public function getErrorCode()
    {
        return self::ERROR_STATUS;
    }
}
