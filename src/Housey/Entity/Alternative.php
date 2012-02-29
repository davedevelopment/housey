<?php

namespace Housey\Entity;

/**
 * An alternative entity class. An alternative would be one of the options to
 * show a client in the test
 *
 * @author      Dave Marshall <david.marshall@atstsolutions.co.uk>
 */
class Alternative 
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var int
     */
    public $experimentId;

    /**
     * @var string
     */
    public $content;

    /**
     * @var string
     */
    public $lookup;

    /**
     * @var int
     */
    public $weight = 1;

    /**
     * @var int
     */
    public $participants = 0;

    /**
     * @var int
     */
    public $conversions = 0;

}



