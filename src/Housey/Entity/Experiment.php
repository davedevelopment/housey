<?php

namespace Housey\Entity;

/**
 * An experiment entity
 *
 * @author      Dave Marshall <david.marshall@atstsolutions.co.uk>
 */
class Experiment
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var string
     */
    public $testName;

    /**
     * @var string
     */
    public $status;

    /**
     * @var DateTime
     */
    public $modified;

    /**
     * @var DateTime
     */
    public $created;

    /**
     * Constructor
     *
     */
    public function __construct()
    {
        $this->modified = new \DateTime;
        $this->created  = new \DateTime;
    }
}



