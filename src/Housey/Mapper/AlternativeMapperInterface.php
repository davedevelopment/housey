<?php

namespace Housey\Mapper;

use Housey\Entity\Alternative;

/**
 * @author      Dave Marshall <david.marshall@atstsolutions.co.uk>
 */
interface AlternativeMapperInterface 
{
    /**
     * @param Alternative $alternative
     * @return bool
     */
    public function insert(Alternative $alternative);

    /**
     * @param Alternative $alternative
     * @return bool
     */
    public function update(Alternative $alternative);

    /**
     * @param Alternative $alternative
     * @return bool
     */
    public function delete(Alternative $alternative);

    /**
     * @param int $experimentId
     * @return bool
     */
    public function deleteByExperimentId($experimentId);

    /**
     * @param int|string $id
     * @return null|Alternative
     */
    public function find($id);

    /**
     * @return Alternative[]
     */
    public function getAll();

    /**
     * @param string $testName
     * @return Alternative[]
     */
    public function getByExperimentTestName($testName);

    /**
     * Increment participatation
     *
     * @param string $lookup
     * @return bool
     */
    public function incrementParticipants($lookup);

    /**
     * Increment conversion
     *
     * @param string $lookup
     * @return bool
     */
    public function incrementConversions($lookup);
    

}


