<?php

namespace Tworzenieweb\SqlProvisioner\Model;

use Symfony\Component\Finder\SplFileInfo;

/**
 * @author Luke Adamczewski
 * @package Tworzenieweb\SqlProvisioner\Model
 */
class CandidateBuilder
{
    const DELIMITER_PATTERN = '/-{2}\s*\/{2}\s*@\s?UNDO/';



    /**
     * @param SplFileInfo $fileInfo
     * @return Candidate
     */
    public function build(SplFileInfo $fileInfo)
    {
        $content = $fileInfo->getContents();
        preg_match(self::DELIMITER_PATTERN, $content, $matches, PREG_OFFSET_CAPTURE);

        if (!empty($matches)) {
            $content = substr($content, 0, $matches[0][1] - 1);
        }

        return new Candidate($fileInfo->getFilename(), $content);
    }
}
