<?php


namespace Tworzenieweb\SqlProvisioner\Model;


use LogicException;

/**
 * Class Exception
 * @package Tworzenieweb\SqlProvisioner\Model
 */
class Exception extends LogicException
{
    /**
     * @param $state
     * @param array $supportedStates
     * @return Exception
     */
    public static function unsupportedCandidateState($state, array $supportedStates)
    {
        return new self(sprintf(
            'Provided candidate state %s is not supported. Supported states: %s',
            $state,
            implode(',', $supportedStates)
        ));
    }


    /**
     * @param string $name
     * @return Exception
     */
    public static function wrongFilename($name)
    {
        return new self(sprintf('Provided file %s is not matching Candidate format', $name));
    }
}