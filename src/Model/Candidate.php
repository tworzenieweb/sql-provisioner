<?php

namespace Tworzenieweb\SqlProvisioner\Model;

/**
 * @author Luke Adamczewski
 * @package Tworzenieweb\SqlProvisioner\Model
 */
class Candidate
{
    const STATUS_QUEUED = 'QUEUED';
    const STATUS_PENDING = 'PENDING';
    const STATUS_ALREADY_DEPLOYED = 'ALREADY_DEPLOYED';
    const STATUS_HAS_SYNTAX_ERROR = 'HAS_SYNTAX_ERROR';

    const FILES_MASK = '/^\d{3,}\_.*\.sql$/';

    private static $supportedStates = [self::STATUS_PENDING, self::STATUS_QUEUED, self::STATUS_ALREADY_DEPLOYED, self::STATUS_HAS_SYNTAX_ERROR];

    /** @var string */
    private $name;

    /** @var string */
    private $content;

    /** @var string */
    private $status;

    /** @var integer */
    private $index;

    /** @var int */
    private $number;

    /** @var boolean */
    private $ignored;


    /**
     * @param string $name
     * @param string $content
     * @throws Exception
     */
    public function __construct($name, $content)
    {

        if (!preg_match(self::FILES_MASK, $name)) {
            throw Exception::wrongFilename($name);
        }

        $this->name = $name;
        $this->number = (int) explode('_', $name)[0];
        $this->content = $content;
        $this->status = self::STATUS_PENDING;
        $this->ignored = false;
    }


    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }


    /**
     * @return void
     */
    public function markAsQueued()
    {
        $this->changeState(self::STATUS_QUEUED);
    }



    /**
     * @param string $causeStatus
     */
    public function markAsIgnored($causeStatus)
    {
        $this->ignored = true;
        $this->changeState($causeStatus);
        $this->content = null;
    }



    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }



    /**
     * @param int $index
     */
    public function setIndex($index)
    {
        $this->index = (int) $index;
    }



    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }



    /**
     * @return int
     */
    public function getIndex()
    {
        return $this->index;
    }



    /**
     * @return int
     */
    public function getNumber()
    {
        return $this->number;
    }


    /**
     * @return bool
     */
    public function isIgnored()
    {
        return $this->ignored;
    }

    /**
     * @return bool
     */
    public function isQueued()
    {
        return $this->status === self::STATUS_QUEUED;
    }

    /**
     * @return bool
     */
    public function isPending()
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * @return bool
     */
    public function isAlreadyDeployed()
    {
        return $this->status === self::STATUS_ALREADY_DEPLOYED;
    }

    /**
     * @param string $newState
     */
    private function changeState($newState)
    {
        if (!in_array($newState, self::$supportedStates)) {
            throw Exception::unsupportedCandidateState($newState, self::$supportedStates);
        }

        $this->status = $newState;
    }
}
