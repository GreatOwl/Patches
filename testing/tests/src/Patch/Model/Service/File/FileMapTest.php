<?php
/**
 * @copyright ©2005—2015 Quicken Loans Inc. All rights reserved. Trade Secret, Confidential and Proprietary. Any
 *     dissemination outside of Quicken Loans is strictly prohibited.
 */

namespace src\Patch\Model\Service\File;


use TallTree\Roots\Patch\Model\Service\File\FileMap;

class FileMapTest extends \PHPUnit_Framework_TestCase
{

    private function createFileSystem()
    {
        $fileSystem = $this->getMockBuilder('League\Flysystem\Filesystem')
            ->disableOriginalConstructor()
            ->getMock();
        return $fileSystem;
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
        $fileSystem = $this->createFileSystem();

        $dbDir = 'some/dir/';
        $tableName = 'someTable';
        $filePath = $dbDir . 'Patches/' . $tableName . '.json';

        $expectedPatches = [
            [
                'query' => 'some query',
                'rollBack' => 'the anti query'
            ],
            [
                'query' => 'another query',
                'rollBack' => 'query of undoing'
            ]
        ];

        $expectedReturnPatches = [
            [
                'query' => 'some query',
                'rollBack' => 'the anti query',
                'patch' => 0,
                'table' => 'someTable'
            ],
            [
                'query' => 'another query',
                'rollBack' => 'query of undoing',
                'patch' => 1,
                'table' => 'someTable'
            ]
        ];

        $fileSystem->expects($this->once())
            ->method('read')
            ->with($this->equalTo($filePath))
            ->will($this->returnValue(json_encode($expectedPatches)));

        $fileMap = new FileMap($fileSystem, $dbDir);

        $this->assertEquals($expectedReturnPatches, $fileMap->getPatches($tableName));
    }

    public function testApplyPatch()
    {
        $fileSystem = $this->createFileSystem();
        $patch = $this->createPatch();

        $dbDir = 'some/dir/';
        $tableName = 'someTable';
        $filePath = $dbDir . 'Patches/' . $tableName . '.json';
        $index = 0;

        $expectedPatches = [
            [
                'query' => 'some query',
                'rollBack' => 'the anti query'
            ],
            [
                'query' => 'another query',
                'rollBack' => 'query of undoing'
            ]
        ];

        $expectedReturnPatches = [
            [
                'query' => 'some query',
                'rollBack' => 'the anti query',
                'patch' => 0,
                'table' => 'someTable'
            ],
            [
                'query' => 'another query',
                'rollBack' => 'query of undoing'
            ]
        ];

        $patch->expects($this->once())
            ->method('getTable')
            ->will($this->returnValue($tableName));
        $patch->expects($this->once())
            ->method('getPatch')
            ->will($this->returnValue($index));
        $patch->expects($this->once())
            ->method('dump')
            ->will($this->returnValue($expectedReturnPatches[$index]));

        $fileSystem->expects($this->once())
            ->method('read')
            ->with($this->equalTo($filePath))
            ->will($this->returnValue(json_encode($expectedPatches)));
        $fileSystem->expects($this->once())
            ->method('delete')
            ->with($this->equalTo($filePath));
        $fileSystem->expects($this->once())
            ->method('write')
            ->with($this->equalTo($filePath), $this->equalTo(json_encode($expectedReturnPatches, JSON_PRETTY_PRINT)));

        $fileMap = new FileMap($fileSystem, $dbDir);

        $fileMap->applyPatch($patch);
    }

    public function testUpdatePatch()
    {
        $fileSystem = $this->createFileSystem();
        $originalPatch = $this->createPatch();
        $newPatch = $this->createPatch();

        $dbDir = 'some/dir/';
        $tableName = 'someTable';
        $filePath = $dbDir . 'Patches/' . $tableName . '.json';
        $index = 0;
        $id = 1234;

        $expectedPatches = [
            [
                'id' => $id,
                'query' => 'some query',
                'rollBack' => 'the anti query',
                'patch' => 0,
                'table' => 'someTable'
            ],
            [
                'query' => 'another query',
                'rollBack' => 'query of undoing'
            ]
        ];

        $expectedReturnPatches = [
            [
                'id' => $id,
                'query' => 'some query',
                'rollBack' => 'the anti query updated',
                'patch' => 0,
                'table' => 'someTable'
            ],
            [
                'query' => 'another query',
                'rollBack' => 'query of undoing'
            ]
        ];

        $originalPatch->expects($this->once())
            ->method('getTable')
            ->will($this->returnValue($tableName));
        $originalPatch->expects($this->once())
            ->method('getPatch')
            ->will($this->returnValue($index));
        $originalPatch->expects($this->once())
            ->method('dump')
            ->will($this->returnValue($expectedPatches[$index]));
        $originalPatch->expects($this->once())
            ->method('getId')
            ->will($this->returnValue($id));

        $newPatch->expects($this->once())
            ->method('dump')
            ->will($this->returnValue($expectedReturnPatches[$index]));

        $fileSystem->expects($this->once())
            ->method('read')
            ->with($this->equalTo($filePath))
            ->will($this->returnValue(json_encode($expectedPatches)));
        $fileSystem->expects($this->once())
            ->method('delete')
            ->with($this->equalTo($filePath));
        $fileSystem->expects($this->once())
            ->method('write')
            ->with($this->equalTo($filePath), $this->equalTo(json_encode($expectedReturnPatches, JSON_PRETTY_PRINT)));

        $fileMap = new FileMap($fileSystem, $dbDir);

        $fileMap->updatePatch($originalPatch, $newPatch);
    }
}
