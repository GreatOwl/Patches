<?php
namespace TallTree\Roots\Patch;

use TallTree\Roots\Patch\Model\Patch;
use TallTree\Roots\Patch\Model\Collection;

class RepositoryTest extends \PHPUnit_Framework_TestCase
{


    private function createMap()
    {
        $map = $this->getMockBuilder('TallTree\Roots\Patch\Model\Service\Map')
            ->disableOriginalConstructor()
            ->getMock();
        return $map;
    }

    private function createFactory()
    {
        $factory = $this->getMockBuilder('TallTree\Roots\Patch\Factory')
            ->getMock();
        return $factory;
    }

    private function createPatch()
    {
        $patch = $this->getMockBuilder('TallTree\Roots\Patch\Model\Patch')
            ->disableOriginalConstructor()
            ->getMock();
        return $patch;
    }

    private function createCollection()
    {
        $collection = $this->getMockBuilder('TallTree\Roots\Patch\Model\Collection')
            ->disableOriginalConstructor()
            ->getMock();
        return $collection;
    }

    public function methodProvider()
    {
        return [
            'db method' => ['method' => 'buildPatchesFromDatabase', 'db' => true],
            'file method' => ['method' => 'buildPatchesFromFile', 'db' => false]
        ];
    }

    /**
     * @var string $method
     * @var boolean $db
     * @dataProvider methodProvider
     */
    public function testBuildPatchesFromDatabase($method, $db)
    {
        $factory = $this->createFactory();
        $fileMap = $this->createMap();
        $dbMap = $this->createMap();

        $table = 'someTable';

        $rawPatches = [
            ['these'], ['are'], ['all'], ['fully'], ['valid'], ['patches'], ['I'], ['swear']
        ];

        if ($db) {
            $mainMap = $dbMap;
        } else {
            $mainMap = $fileMap;
        }

        $mainMap->expects($this->once())
            ->method('getPatches')
            ->with($this->equalTo($table))
            ->will($this->returnValue($rawPatches));

        $patches = [];
        $factoryCount = 0;
        foreach ($rawPatches as $patch) {
            $mockPatch = $this->createPatch();
            $factory->expects($this->at($factoryCount++))
                    ->method('createPatch')
                    ->will($this->returnValue($mockPatch));
            $patches[] =$mockPatch;
        }

        $collection = $this->checkRequirements();
        $factory->expects($this->at($factoryCount++))
            ->method('createCollection')
            ->with($this->equalTo($patches))
            ->will($this->returnValue($collection));

        $repository = new Repository($dbMap, $fileMap, $factory);

        $this->assertEquals($collection, call_user_func_array([$repository, $method], [$table]));


    }
}
