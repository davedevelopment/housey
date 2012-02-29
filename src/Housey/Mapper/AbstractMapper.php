<?php

namespace Housey\Mapper;

use Doctrine\DBAL\Connection;

/**
 * Abstract DBAL mapper
 *
 * @author      Dave Marshall <david.marshall@atstsolutions.co.uk>
 */
class AbstractMapper
{
    /**
     * @var Connection
     */
    protected $connection;

    /**
     * Constrcutor
     *
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }


}



