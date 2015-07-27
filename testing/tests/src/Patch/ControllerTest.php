<?php
/**
 * @copyright ©2005—2015 Quicken Loans Inc. All rights reserved. Trade Secret, Confidential and Proprietary. Any
 *     dissemination outside of Quicken Loans is strictly prohibited.
 */

namespace TallTree\Roots\Patch;

class ControllerTest extends \PHPUnit_Framework_TestCase
{

    private function createRepository()
    {
        $repository = $this->getMockBuilder('TallTree\Roots\Patch\Repository')
            ->disableOriginalConstructor()
            ->getMock();
        return $repository;
    }

    private function createQuery()
    {
        $query = $this->getMockBuilder('TallTree\Roots\Service\Database\Query')
            ->disableOriginalConstructor()
            ->getMock();
        return $query;
    }

    private function createMap()
    {
        $map = $this->getMockBuilder('TallTree\Roots\Patch\Model\Service\Map')
            ->disableOriginalConstructor()
            ->getMock();
        return $map;
    }

    private function createFileHandle()
    {
        $handle = $this->getMockBuilder('TallTree\Roots\Service\File\Handle')
            ->disableOriginalConstructor()
            ->getMock();
        return $handle;
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

    public function testPatchTablePatchErrors()
    {
        $repo = $this->createRepository();
        $query = $this->createQuery();
        $handle = $this->createFileHandle();
        $fileMap = $this->createMap();
        $dbMap = $this->createMap();

        $table = 'someTable';
        $queryString = 'someQuery';
        $error = ['something 0', 'something 1', 'something 2'];

        $dbCollection = $this->createCollection();
        $fileCollection = $this->createCollection();

        $patch = $this->createPatch();
        $patch->expects($this->once())
            ->method('getQuery')
            ->will($this->returnValue($queryString));

        $query->expects($this->once())
            ->method('patch')
            ->with($this->equalTo($queryString))
            ->will($this->returnValue($error));

        $fileCollection->expects($this->once())
            ->method('diff');
        $fileCollection->expects($this->once())
            ->method('getIterator')
            ->will($this->returnValue(new \ArrayIterator([$patch])));

        $repo->expects($this->once())
            ->method('buildPatchesFromDatabase')
            ->with($this->equalTo($table))
            ->will($this->returnValue($dbCollection));
        $repo->expects($this->once())
            ->method('buildPatchesFromFile')
            ->with($this->equalTo($table))
            ->will($this->returnValue($fileCollection));

        $controller = new Controller($repo, $query, $handle, $dbMap, $fileMap);

        $controller->patchTable($table);
    }

    public function testPatchTablePatchNoErrors()
    {
        $repo = $this->createRepository();
        $query = $this->createQuery();
        $handle = $this->createFileHandle();
        $fileMap = $this->createMap();
        $dbMap = $this->createMap();

        $table = 'someTable';
        $queryString = 'someQuery';
        $error = ['something 0', 'something 1', null];

        $dbCollection = $this->createCollection();
        $fileCollection = $this->createCollection();

        $patch = $this->createPatch();
        $patch->expects($this->once())
            ->method('getQuery')
            ->will($this->returnValue($queryString));

        $query->expects($this->once())
            ->method('patch')
            ->with($this->equalTo($queryString))
            ->will($this->returnValue($error));

        $fileCollection->expects($this->once())
            ->method('diff');
        $fileCollection->expects($this->once())
            ->method('getIterator')
            ->will($this->returnValue(new \ArrayIterator([$patch])));

        $repo->expects($this->once())
            ->method('buildPatchesFromDatabase')
            ->with($this->equalTo($table))
            ->will($this->returnValue($dbCollection));
        $repo->expects($this->once())
            ->method('buildPatchesFromFile')
            ->with($this->equalTo($table))
            ->will($this->returnValue($fileCollection));

        $dbMap->expects($this->once())
            ->method('applyPatch')
            ->with($this->equalTo($patch));
        $fileMap->expects($this->once())
            ->method('applyPatch')
            ->with($this->equalTo($patch));

        $controller = new Controller($repo, $query, $handle, $dbMap, $fileMap);

        $controller->patchTable($table);
    }

    public function testPatchTablePatchAll()
    {
        $repo = $this->createRepository();
        $query = $this->createQuery();
        $handle = $this->createFileHandle();
        $fileMap = $this->createMap();
        $dbMap = $this->createMap();

        $table = 'someTable';
        $queryString = 'someQuery';
        $error = ['something 0', 'something 1', null];

        $dbCollection = $this->createCollection();
        $fileCollection = $this->createCollection();

        $patch = $this->createPatch();
        $patch->expects($this->once())
            ->method('getQuery')
            ->will($this->returnValue($queryString));

        $query->expects($this->once())
            ->method('patch')
            ->with($this->equalTo($queryString))
            ->will($this->returnValue($error));

        $fileCollection->expects($this->once())
            ->method('diff');
        $fileCollection->expects($this->once())
            ->method('getIterator')
            ->will($this->returnValue(new \ArrayIterator([$patch])));

        $repo->expects($this->once())
            ->method('buildPatchesFromDatabase')
            ->with($this->equalTo($table))
            ->will($this->returnValue($dbCollection));
        $repo->expects($this->once())
            ->method('buildPatchesFromFile')
            ->with($this->equalTo($table))
            ->will($this->returnValue($fileCollection));

        $dbMap->expects($this->once())
            ->method('applyPatch')
            ->with($this->equalTo($patch));
        $fileMap->expects($this->once())
            ->method('applyPatch')
            ->with($this->equalTo($patch));

        $handle->expects($this->once())
            ->method('getAllFilesInDir')
            ->will($this->returnValue([$table]));

        $controller = new Controller($repo, $query, $handle, $dbMap, $fileMap);

        $controller->patchAll();
    }
}
