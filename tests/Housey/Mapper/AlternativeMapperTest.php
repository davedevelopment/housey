<?php
namespace Housey\Mapper;

use Housey\Entity\Alternative;
use Doctrine\DBAL\Connection;

/**
 * Not a lot going on in here, just sanity stuff really
 */
class AlternativeMapperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AlternativeMapper
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->mockConnection = $this->getMock("\\Doctrine\\DBAL\\Connection", array(), array(), '', false);
        $this->object = new AlternativeMapper($this->mockConnection);
    }

    protected function getAlternativeStub()
    {
        $alt = new Alternative;
        $alt->id = 43;
        $alt->experimentId = 12;
        $alt->content = 'My content';
        $alt->lookup = 'mylookup';
        $alt->weight = 1;
        $alt->participants = 1234;
        $alt->conversions = 123;
        return $alt;
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
    }

    /**
     * @covers {className}::{origMethodName}
     * @todo Implement testInsert().
     */
    public function testInsert()
    {
        $this->mockConnection->expects($this->once())
                             ->method('insert')
                             ->will($this->returnValue(1));

        $this->mockConnection->expects($this->once())
                             ->method('lastInsertId');

        $this->object->insert($this->getAlternativeStub());
    }

    /**
     * @covers {className}::{origMethodName}
     * @todo Implement testUpdate().
     */
    public function testUpdate()
    {
        $this->mockConnection->expects($this->once())
                             ->method('update');

        $this->object->update($this->getAlternativeStub());
    }

    /**
     * @covers {className}::{origMethodName}
     * @todo Implement testDelete().
     */
    public function testDelete()
    {
        $this->mockConnection->expects($this->once())
                             ->method('delete');

        $this->object->delete($this->getAlternativeStub());
    }

    /**
     * @covers {className}::{origMethodName}
     * @todo Implement testFind().
     */
    public function testFind()
    {
        $this->mockConnection->expects($this->once())
                             ->method('fetchAssoc');

        $this->object->find(12);
    }

    /**
     * @covers {className}::{origMethodName}
     * @todo Implement testGetAll().
     */
    public function testGetAll()
    {
        $this->mockConnection->expects($this->once())
                             ->method('fetchAll')
                             ->will($this->returnValue(array()));

        $this->object->getAll();
    }

    /**
     * @covers {className}::{origMethodName}
     * @todo Implement testRowToEntity().
     */
    public function testRowToEntity()
    {
        $row = array(
            'id' => "123",
            'housey_experiment_id' => "12",
            'content' => 'dave',
            'lookup' => 'lookdave',
            'weight' => "456",
            'participants' => "656",
            'conversions' => "650",
        );

        $actual = AlternativeMapper::rowToEntity($row);
        $this->assertEquals(123, $actual->id);
        $this->assertEquals(12, $actual->experimentId);
        $this->assertEquals('dave', $actual->content);
        $this->assertEquals('lookdave', $actual->lookup);
        $this->assertEquals(456, $actual->weight);
        $this->assertEquals(656, $actual->participants);
        $this->assertEquals(650, $actual->conversions);
    }

    /**
     * @covers {className}::{origMethodName}
     * @todo Implement testEntityToRow().
     */
    public function testEntityToRow()
    {
        $alternative = $this->getAlternativeStub();
        $actual = AlternativeMapper::entityToRow($alternative);
        $expected = array(
            'id' => $alternative->id,
            'housey_experiment_id' => $alternative->experimentId,
            'content' => $alternative->content,
            'lookup' => $alternative->lookup,
            'weight' => $alternative->weight,
            'participants' => $alternative->participants,
            'conversions' => $alternative->conversions,
        );

        $this->assertEquals($actual, $expected);
    }
}
?>
