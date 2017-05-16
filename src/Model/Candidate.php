<?php

namespace Tworzenieweb\SqlProvisioner\Model;

/**
 * @author Luke Adamczewski
 * @package Tworzenieweb\SqlProvisioner\Model
 */
class Candidate
{
    const STATUS_QUEUED = 'QUEUED';

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



    /**
     * @param string $name
     * @param string $content
     */
    public function __construct($name, $content)
    {
        $this->name = $name;
        $this->number = (int) explode('_', $name)[0];
        $this->content = $content;
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
        $this->status = self::STATUS_QUEUED;
    }



    /**
     * @param string $status
     */
    public function markAsIgnored($status)
    {
        $this->status = $status;
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
    public function isQueued()
    {
        return $this->status === self::STATUS_QUEUED;
    }
}