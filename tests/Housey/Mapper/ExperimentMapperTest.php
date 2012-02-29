<?php
namespace Housey\Mapper;

use Housey\Entity\Experiment;
use Doctrine\DBAL\Connection;

/**
 * Not a lot going on in here, just sanity stuff really
 */
class ExperimentMapperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ExperimentMapper
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->mockConnection = $this->getMock("\\Doctrine\\DBAL\\Connection", array(), array(), '', false);
        $this->object = new ExperimentMapper($this->mockConnection);
    }

    protected function getExperimentStub()
    {
        $alt = new Experiment;
        $alt->id = 43;
        $alt->testName = 'my test';
        $alt->status = 'live';
        $alt->created = new \DateTime();
        $alt->modified = new \DateTime();
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

        $this->object->insert($this->getExperimentStub());
    }

    /**
     * @covers {className}::{origMethodName}
     * @todo Implement testUpdate().
     */
    public function testUpdate()
    {
        $this->mockConnection->expects($this->once())
                             ->method('update');

        $this->object->update($this->getExperimentStub());
    }

    /**
     * @covers {className}::{origMethodName}
     * @todo Implement testDelete().
     */
    public function testDelete()
    {
        $this->mockConnection->expects($this->once())
                             ->method('delete');

        $this->object->delete($this->getExperimentStub());
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
            'test_name' => 'dave',
            'status' => 'live',
            'created' => "2011-12-21 14:57:00",
            'modified' => "2011-12-21 14:58:00",
        );

        $actual = ExperimentMapper::rowToEntity($row);
        $this->assertEquals(123, $actual->id);
        $this->assertEquals('dave', $actual->testName);
        $this->assertEquals('live', $actual->status);
        $this->assertEquals(new \DateTime("2011-12-21 14:57:00"), $actual->created);
        $this->assertEquals(new \DateTime("2011-12-21 14:58:00"), $actual->modified);
    }

    /**
     * @covers {className}::{origMethodName}
     * @todo Implement testEntityToRow().
     */
    public function testEntityToRow()
    {
        $experiment = $this->getExperimentStub();
        $actual = ExperimentMapper::entityToRow($experiment);
        $expected = array(
            'id' => $experiment->id,
            'test_name' => $experiment->testName,
            'status' => $experiment->status,
            'created' => $experiment->created->format("Y-m-d H:i:s"),
            'modified' => $experiment->modified->format("Y-m-d H:i:s"),
        );

        $this->assertEquals($actual, $expected);
    }
}
?>
