<?php

namespace Housey\Mapper;

use Housey\Entity\Alternative;

/**
 * @author      Dave Marshall <david.marshall@atstsolutions.co.uk>
 */
class AlternativeMapper extends AbstractMapper implements AlternativeMapperInterface
{
    /**
     * @param Alternative $alternative
     * @return bool
     */
    public function insert(Alternative $alternative) 
    {
        $ret = $this->connection->insert('housey_alternatives', static::entityToRow($alternative));
        if ($ret) {
            $alternative->id = $this->connection->lastInsertId();
        }
        return $ret;
    }

    /**
     * @param Alternative $alternative
     * @return bool
     */
    public function update(Alternative $alternative)
    {
        return $this->connection->update('housey_alternatives', static::entityToRow($alternative), array('id' => $alternative->id));
    }

    /**
     * @param Alternative $alternative
     * @return bool
     */
    public function delete(Alternative $alternative)
    {
        return $this->connection->delete('housey_alternatives', array('id' => $alternative->id));
    }

    /**
     * @param int $experimentId
     * @return bool
     */
    public function deleteByExperimentId($experimentId)
    {
        return $this->connection->delete('housey_alternatives', array('housey_experiment_id' => $experimentId));
    }

    /**
     * @param int|string $id
     * @return null|Alternative
     */
    public function find($id)
    {
        if (is_numeric($id)) {
            $column = 'id';
        } else {
            $column = 'lookup';
        }
        $row = $this->connection->fetchAssoc("SELECT * FROM housey_alternatives WHERE $column = ?", array($id));

        if (null == $row) {
            return null;
        }
        return static::rowToEntity($row);
    }

    /**
     * Increment participatation
     *
     * @param string $lookup
     * @return bool
     */
    public function incrementParticipants($lookup)
    {
        return $this->connection->executeUpdate("UPDATE housey_alternatives SET participants = participants + 1 WHERE lookup = ?", array($lookup));
    }

    /**
     * Increment conversion
     *
     * @param string $lookup
     * @return bool
     */
    public function incrementConversions($lookup)
    {
        return $this->connection->executeUpdate("UPDATE housey_alternatives SET conversions = conversions + 1 WHERE lookup = ?", array($lookup));
    }

    /**
     * @return Alternative[]
     */
    public function getAll() 
    {
        $all = $this->connection->fetchAll('SELECT * FROM housey_alternatives');
        $entities = array();
        foreach($all as $row) {
            $entities[] = static::rowToEntity($row);
        }
        return $entities;
    }

    /**
     * @param string $testName
     * @return Alternative[]
     */
    public function getByExperimentTestName($testName)
    {
        $all = $this->connection->fetchAll('SELECT a.* FROM housey_alternatives a LEFT JOIN housey_experiments e ON a.housey_experiment_id = e.id WHERE e.test_name = ? ', array($testName));
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
     * @return Alternative
     */
    public static function rowToEntity(array $row)
    {
        $alternative = new Alternative;
        $alternative->id = !empty($row['id']) ? (int) $row['id'] : null;
        $alternative->experimentId = (int) $row['housey_experiment_id'];
        $alternative->content = $row['content'];
        $alternative->lookup = $row['lookup'];
        $alternative->weight = (int) $row['weight'];
        $alternative->participants = (int) $row['participants'];
        $alternative->conversions = (int) $row['conversions'];
        return $alternative;
    }

    /**
     * Good for RDBMS, could move to a helper to share with other mapper
     * implementations etc.
     *
     * @param array $alternative
     * @return array
     */
    public static function entityToRow(Alternative $alternative)
    {
        return array(
            'id' => $alternative->id,
            'housey_experiment_id' => $alternative->experimentId,
            'content' => $alternative->content,
            'lookup' => $alternative->lookup,
            'weight' => $alternative->weight,
            'participants' => $alternative->participants,
            'conversions' => $alternative->conversions,
        );
    }
}


