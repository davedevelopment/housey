<?php

namespace Housey\Mapper;

use Housey\Entity\Experiment;

/**
 * @author      Dave Marshall <david.marshall@atstsolutions.co.uk>
 */
interface ExperimentMapperInterface 
{
    /**
     * @param Experiment $experiment
     * @return bool
     */
    public function insert(Experiment $experiment);

    /**
     * @param Experiment $experiment
     * @return bool
     */
    public function update(Experiment $experiment);

    /**
     * @param Experiment $experiment
     * @return bool
     */
    public function delete(Experiment $experiment);

    /**
     * @param int|string $id
     * @return null|Experiment
     */
    public function find($id);

    /**
     * @return Experiment[]
     */
    public function getAll();
}


