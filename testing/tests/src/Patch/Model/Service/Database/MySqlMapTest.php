<?php
namespace src\Patch\Model\Service\Database;


use TallTree\Roots\Patch\Model\Service\Database\MySqlMap;

class MySqlMapTest extends \PHPUnit_Framework_TestCase
{

    private function createQuery()
    {
        $query = $this->getMockBuilder('TallTree\Roots\Service\Database\Query')
            ->disableOriginalConstructor()
            ->getMock();
        return $query;
    }

    private function createPatch()
    {
        $patch = $this->getMockBuilder('TallTree\Roots\Patch\Model\Patch')
            ->disableOriginalConstructor()
            ->getMock();
        return $patch;
    }

    public function testGetPatches()
    {
        $query = $this->createQuery();

        $input = ['table' => 'some table'];
        $results = ['table of results'];

        $query->expects($this->once())
            ->method('read')
            ->with($this->equalTo(MySqlMap::SELECT_PATCHES_TABLE), $this->equalTo($input))
            ->will($this->returnValue($results));

        $mySqlMap = new MySqlMap($query);

        $this->assertEquals($results, $mySqlMap->getPatches($input['table']));
    }

    public function testApplyPatch()
    {
        $query = $this->createQuery();
        $patch = $this->createPatch();

        $dump = [
            'field1' => 'value1',
            'field2' => 'value2',
            'field3' => 'value3'
        ];

        $setQuery = '`field1` = :field1 ,`field2` = :field2 ,`field3` = :field3 ';
        $builtQuery = sprintf(MySqlMap::APPLY_PATCH, $setQuery);

        $patch->expects($this->once())
            ->method('dump')
            ->will($this->returnValue($dump));

        $query->expects($this->once())
            ->method('write')
            ->with($this->equalTo($builtQuery), $this->equalTo($dump));

        $mySqlMap = new MySqlMap($query);

        $mySqlMap->applyPatch($patch);
    }

    public function testUpdatePatch()
    {
        $query = $this->createQuery();
        $originalPatch = $this->createPatch();
        $newPatch = $this->createPatch();

        $id = 1234;

        $originalDump = [
            'field1' => 'value1',
            'field2' => 'value2',
            'field3' => 'value3'
        ];

        $newDump = [
            'field1' => 'value1',
            'field2' => 'value4',
            'field3' => null,
            'field4' => 'value5'
        ];

        $expectedDiff = [
            'field2' => 'value4',
            'field3' => null,
            'field4' => 'value5'
        ];

        $newPatch->expects($this->once())
            ->method('dump')
            ->will($this->returnValue($newDump));
        $originalPatch->expects($this->once())
            ->method('dump')
            ->will($this->returnValue($originalDump));
        $originalPatch->expects($this->once())
            ->method('getId')
            ->will($this->returnValue($id));

        $expectedDiff['id'] = $id;
        $setQuery = '`field2` = :field2 ,`field3` = :field3 ,`field4` = :field4 ,`id` = :id ';
        $builtQuery = sprintf(MySqlMap::UPDATE_PATCH, $setQuery);

        $query->expects($this->once())
            ->method('write')
            ->with($this->equalTo($builtQuery), $this->equalTo($expectedDiff));

        $mySqlMap = new MySqlMap($query);

        $mySqlMap->updatePatch($originalPatch, $newPatch);
    }
}
