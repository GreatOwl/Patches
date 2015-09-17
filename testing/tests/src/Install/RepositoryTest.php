<?php

namespace TallTree\Roots\Install;

class RepositoryTest extends \PHPUnit_Framework_TestCase
{

    private function createMap()
    {
        $map = $this->getMockBuilder('TallTree\Roots\Install\Model\Service\Map')
            ->disableOriginalConstructor()
            ->getMock();
        return $map;
    }

    private function createFactory()
    {
        $factory = $this->getMockBuilder('TallTree\Roots\Install\Factory')
            ->disableOriginalConstructor()
            ->getMock();
        return $factory;
    }

    private function createInstall()
    {
        $install = $this->getMockBuilder('TallTree\Roots\Install\Model\Install')
            ->disableOriginalConstructor()
            ->getMock();
        return $install;
    }

    public function testBuildInstallFromDBNull()
    {
        $table = 'someTable';
        $dbMap = $this->createMap();
        $fileMap = $this->createMap();
        $factory = $this->createFactory();

        $dbMap->expects($this->once())
            ->method('getInstall')
            ->with($table)
            ->willReturn(null);

        $repository = new Repository($dbMap, $fileMap, $factory);

        $this->assertNull($repository->buildInstallFromDatabase($table));
    }

    public function testBuildInstallFromDB()
    {
        $table = 'someTable';
        $dbMap = $this->createMap();
        $fileMap = $this->createMap();
        $factory = $this->createFactory();
        $install = $this->createInstall();
        $raw = ['stuff'];

        $dbMap->expects($this->once())
            ->method('getInstall')
            ->with($table)
            ->willReturn($raw);

        $factory->expects($this->once())
            ->method('createInstall')
            ->with($this->equalTo($raw))
            ->willReturn($install);

        $repository = new Repository($dbMap, $fileMap, $factory);

        $this->assertEquals($install, $repository->buildInstallFromDatabase($table));
    }

    public function testBuildInstallFromFileNull()
    {
        $table = 'someTable';
        $dbMap = $this->createMap();
        $fileMap = $this->createMap();
        $factory = $this->createFactory();

        $fileMap->expects($this->once())
            ->method('getInstall')
            ->with($table)
            ->willReturn(null);

        $repository = new Repository($dbMap, $fileMap, $factory);

        $this->assertNull($repository->buildInstallFromFile($table));
    }

    public function testBuildInstallFromFile()
    {
        $table = 'someTable';
        $dbMap = $this->createMap();
        $fileMap = $this->createMap();
        $factory = $this->createFactory();
        $install = $this->createInstall();
        $raw = ['stuff'];

        $fileMap->expects($this->once())
            ->method('getInstall')
            ->with($table)
            ->willReturn($raw);

        $factory->expects($this->once())
            ->method('createInstall')
            ->with($this->equalTo($raw))
            ->willReturn($install);

        $repository = new Repository($dbMap, $fileMap, $factory);

        $this->assertEquals($install, $repository->buildInstallFromFile($table));
    }
}
