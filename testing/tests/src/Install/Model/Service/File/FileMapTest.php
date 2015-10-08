<?php
namespace TallTree\Roots\Install\Model\Service\File;


class FileMapTest extends \PHPUnit_Framework_TestCase
{

    private function createFileSystem()
    {
        $fileSystem = $this->getMockBuilder('League\Flysystem\Filesystem')
            ->disableOriginalConstructor()
            ->getMock();
        return $fileSystem;
    }

    private function createInstall()
    {
        $install = $this->getMockBuilder('TallTree\Roots\Install\Model\Install')
            ->disableOriginalConstructor()
            ->getMock();
        return $install;
    }

    private function createNameSpacesTransform()
    {
        $nameSpaces = $this->getMockBuilder('TallTree\Roots\Service\Transform\NameSpaces')
            ->disableOriginalConstructor()
            ->getMock();
        return $nameSpaces;
    }

    public function testGetInstall()
    {
        $table = 'table';
        $dbDir = 'some/directory/';
        $expectedFilePath = $dbDir . "install/$table.json";
        $expected = ['table' => $table];

        $fileSystem = $this->createFileSystem();
        $nameSpaces = $this->createNameSpacesTransform();

        $fileSystem->expects($this->once())
            ->method('read')
            ->with($this->equalTo($expectedFilePath))
            ->willReturn(json_encode($expected, JSON_PRETTY_PRINT));

        $fileMap = new FileMap($fileSystem, $nameSpaces, $dbDir);

        $this->assertEquals($expected, $fileMap->getInstall($table));
    }

    public function testApplyInstall()
    {
        $table = 'someTable';
        $dbDir = 'some/directory/';
        $expectedFilePath = $dbDir . "install/$table.json";
        $expected = [
            'table' => $table,
            'params' => 'in a table'
        ];

        $fileSystem = $this->createFileSystem();
        $install = $this->createInstall();
        $nameSpaces = $this->createNameSpacesTransform();

        $install->expects($this->once())
            ->method('getTable')
            ->willReturn($table);
        $install->expects($this->once())
            ->method('dump')
            ->willReturn($expected);

        $fileSystem->expects($this->once())
            ->method('delete')
            ->with($this->equalTo($expectedFilePath));
        $fileSystem->expects($this->once())
            ->method('write')
            ->with($this->equalTo($expectedFilePath), json_encode($expected, JSON_PRETTY_PRINT));

        $fileMap = new FileMap($fileSystem, $nameSpaces, $dbDir);

        $fileMap->applyInstall($install);
    }

    public function testUpdateInstall()
    {
        $table = 'someTable';
        $dbDir = 'some/directory/';
        $expectedFilePath = $dbDir . "install/$table.json";
        $expected = [
            'table' => $table,
            'params' => 'in a table'
        ];

        $fileSystem = $this->createFileSystem();
        $originalInstall = $this->createInstall();
        $newInstall = $this->createInstall();
        $nameSpaces = $this->createNameSpacesTransform();

        $originalInstall->expects($this->once())
            ->method('getTable')
            ->willReturn($table);
        $newInstall->expects($this->once())
            ->method('dump')
            ->willReturn($expected);

        $fileSystem->expects($this->once())
            ->method('delete')
            ->with($this->equalTo($expectedFilePath));
        $fileSystem->expects($this->once())
            ->method('write')
            ->with($this->equalTo($expectedFilePath), json_encode($expected, JSON_PRETTY_PRINT));

        $fileMap = new FileMap($fileSystem, $nameSpaces, $dbDir);

        $fileMap->updateInstall($originalInstall, $newInstall);
    }
}
