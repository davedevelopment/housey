<?php

namespace Housey\Mapper;

use Housey\Entity\Experiment;

/**
 * @author      Dave Marshall <david.marshall@atstsolutions.co.uk>
 */
class ExperimentMapper extends AbstractMapper implements ExperimentMapperInterface
{
    /**
     * @param Experiment $experiment
     * @return bool
     */
    public function insert(Experiment $experiment) 
    {
        $ret = $this->connection->insert('housey_experiments', static::entityToRow($experiment));
        if ($ret) {
            $experiment->id = $this->connection->lastInsertId();
        }
        return $ret;
    }

    /**
     * @param Experiment $experiment
     * @return bool
     */
    public function update(Experiment $experiment)
    {
        return $this->connection->update('housey_experiments', static::entityToRow($experiment), array('id' => $experiment->id));
    }

    /**
     * @param Experiment $experiment
     * @return bool
     */
    public function delete(Experiment $experiment)
    {
        return $this->connection->delete('housey_experiments', array('id' => $experiment->id));
    }

    /**
     * @param int|string $id
     * @return null|Experiment
     */
    public function find($id)
    {
        if (is_numeric($id)) {
            $column = 'id';
        } else {
            $column = 'test_name';
        }

        $row = $this->connection->fetchAssoc("SELECT * FROM housey_experiments WHERE $column = ?", array($id));

        if (null == $row) {
            return null;
        }
        return static::rowToEntity($row);
    }

    /**
     * @return Experiment[]
     */
    public function getAll() 
    {
        $all = $this->connection->fetchAll('SELECT * FROM housey_experiments ORDER BY modified DESC');
        $entities = array();
        foreach($all as $row) {
            $entities[] = static::rowToEntity($row);
        }
        return $entities;
    }


    /**
     * Good for RDBMS, could move to a helper to share with other mapper
     * implementations etc.
     *
     * @param array $row
     * @return Experiment
     */
    public static function rowToEntity(array $row)
    {
        $experiment = new Experiment;
        $experiment->id = !empty($row['id']) ? (int) $row['id'] : null;
        $experiment->testName = $row['test_name'];
        $experiment->status = $row['status'];
        $experiment->modified = new \DateTime($row['modified']);
        $experiment->created = new \DateTime($row['created']);
        return $experiment;
    }

    /**
     * Good for RDBMS, could move to a helper to share with other mapper
     * implementations etc.
     *
     * @param array $experiment
     * @return array
     */
    public static function entityToRow(Experiment $experiment)
    {
        return array(
            'id' => $experiment->id,
            'test_name' => $experiment->testName,
            'status' => $experiment->status,
            'modified' => $experiment->modified->format("Y-m-d H:i:s"),
            'created' => $experiment->created->format("Y-m-d H:i:s"),
        );
    }
}


