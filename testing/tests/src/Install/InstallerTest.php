<?php
namespace TallTree\Roots\Install;

class InstallerTest extends \PHPUnit_Framework_TestCase
{

    private function createRepository()
    {
        $repository = $this->getMockBuilder('TallTree\Roots\Install\Repository')
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

    private function createFileHandle()
    {
        $fileHandle = $this->getMockBuilder('TallTree\Roots\Service\File\Handle')
            ->disableOriginalConstructor()
            ->getMock();
        return $fileHandle;
    }

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

    private function createPatch()
    {
        $patch = $this->getMockBuilder('TallTree\Roots\Patch\Model\Patch')
            ->disableOriginalConstructor()
            ->getMock();
        return $patch;
    }

    private function createPatchCollection()
    {
        $collection = $this->getMockBuilder('TallTree\Roots\Patch\Model\Collection')
            ->disableOriginalConstructor()
            ->getMock();
        return $collection;
    }

    public function testInstallTableInstalled()
    {
        $table = 'someTable';
        $repository = $this->createRepository();
        $query = $this->createQuery();
        $fileHandle = $this->createFileHandle();
        $dbMap = $this->createMap();
        $fileMap = $this->createMap();
        $factory = $this->createFactory();
        $installed = $this->createInstall();
        $unInstalled = $this->createInstall();

        $repository->expects($this->once())
            ->method('buildInstallFromDatabase')
            ->with($this->equalTo($table))
            ->willReturn($installed);
        $repository->expects($this->once())
            ->method('buildInstallFromFile')
            ->with($this->equalTo($table))
            ->willReturn($unInstalled);

        $installed->expects($this->once())
            ->method('getInstall')
            ->willReturn('stuff');

        $installer = new Installer(
            $repository,
            $query,
            $fileHandle,
            $dbMap,
            $fileMap,
            $factory
        );

        $installer->installTable($table);
    }

    public function testInstallTableInstalledEmptyUnInstalledEmpty()
    {
        $table = 'someTable';
        $repository = $this->createRepository();
        $query = $this->createQuery();
        $fileHandle = $this->createFileHandle();
        $dbMap = $this->createMap();
        $fileMap = $this->createMap();
        $factory = $this->createFactory();
        $installed = $this->createInstall();
        $unInstalled = $this->createInstall();

        $repository->expects($this->once())
            ->method('buildInstallFromDatabase')
            ->with($this->equalTo($table))
            ->willReturn($installed);
        $repository->expects($this->once())
            ->method('buildInstallFromFile')
            ->with($this->equalTo($table))
            ->willReturn($unInstalled);

        $installed->expects($this->once())
            ->method('getInstall')
            ->willReturn(null);
        $unInstalled->expects($this->once())
            ->method('getInstall')
            ->willReturn(null);

        $installer = new Installer(
            $repository,
            $query,
            $fileHandle,
            $dbMap,
            $fileMap,
            $factory
        );

        $installer->installTable($table);
    }

    public function testInstallTableInstalledEmptyUnInstalledError()
    {
        $table = 'someTable';
        $repository = $this->createRepository();
        $query = $this->createQuery();
        $fileHandle = $this->createFileHandle();
        $dbMap = $this->createMap();
        $fileMap = $this->createMap();
        $factory = $this->createFactory();
        $installed = $this->createInstall();
        $unInstalled = $this->createInstall();
        $installScript = 'stuff';

        $repository->expects($this->once())
            ->method('buildInstallFromDatabase')
            ->with($this->equalTo($table))
            ->willReturn($installed);
        $repository->expects($this->once())
            ->method('buildInstallFromFile')
            ->with($this->equalTo($table))
            ->willReturn($unInstalled);

        $installed->expects($this->once())
            ->method('getInstall')
            ->willReturn(null);
        $unInstalled->expects($this->exactly(2))
            ->method('getInstall')
            ->willReturn($installScript);

        $query->expects($this->once())
            ->method('patch')
            ->with($this->equalTo($installScript))
            ->willReturn(['something', 'somethingElse', 'anotherThing']);

        $installer = new Installer(
            $repository,
            $query,
            $fileHandle,
            $dbMap,
            $fileMap,
            $factory
        );

        $installer->installTable($table);
    }

    public function testInstallTableInstalledEmptyUnInstalledNoError()
    {
        $table = 'someTable';
        $repository = $this->createRepository();
        $query = $this->createQuery();
        $fileHandle = $this->createFileHandle();
        $dbMap = $this->createMap();
        $fileMap = $this->createMap();
        $factory = $this->createFactory();
        $installed = $this->createInstall();
        $unInstalled = $this->createInstall();
        $installScript = 'stuff';

        $repository->expects($this->once())
            ->method('buildInstallFromDatabase')
            ->with($this->equalTo($table))
            ->willReturn($installed);
        $repository->expects($this->once())
            ->method('buildInstallFromFile')
            ->with($this->equalTo($table))
            ->willReturn($unInstalled);

        $installed->expects($this->once())
            ->method('getInstall')
            ->willReturn(null);
        $unInstalled->expects($this->exactly(2))
            ->method('getInstall')
            ->willReturn($installScript);

        $query->expects($this->once())
            ->method('patch')
            ->with($this->equalTo($installScript))
            ->willReturn([2 => null]);

        $dbMap->expects($this->once())
            ->method('applyInstall')
            ->with($this->equalTo($unInstalled));
        $fileMap->expects($this->once())
            ->method('applyInstall')
            ->with($this->equalTo($unInstalled));

        $installer = new Installer(
            $repository,
            $query,
            $fileHandle,
            $dbMap,
            $fileMap,
            $factory
        );

        $installer->installTable($table);
    }

    public function testInstallAllTablesNone()
    {
        $repository = $this->createRepository();
        $query = $this->createQuery();
        $fileHandle = $this->createFileHandle();
        $dbMap = $this->createMap();
        $fileMap = $this->createMap();
        $factory = $this->createFactory();

        $fileHandle->expects($this->once())
            ->method('getAllFilesInDir')
            ->with($this->equalTo('install/'))
            ->willReturn([]);

        $installer = new Installer(
            $repository,
            $query,
            $fileHandle,
            $dbMap,
            $fileMap,
            $factory
        );

        $installer->installAll();
    }

    public function testInstallAllTables()
    {
        $table = 'someTable';
        $tables = [$table];
        $repository = $this->createRepository();
        $query = $this->createQuery();
        $fileHandle = $this->createFileHandle();
        $dbMap = $this->createMap();
        $fileMap = $this->createMap();
        $factory = $this->createFactory();
        $installed = $this->createInstall();
        $unInstalled = $this->createInstall();
        $installScript = 'stuff';

        $fileHandle->expects($this->once())
            ->method('getAllFilesInDir')
            ->with($this->equalTo('install/'))
            ->willReturn($tables);

        $repository->expects($this->once())
            ->method('buildInstallFromDatabase')
            ->with($this->equalTo($table))
            ->willReturn($installed);
        $repository->expects($this->once())
            ->method('buildInstallFromFile')
            ->with($this->equalTo($table))
            ->willReturn($unInstalled);

        $installed->expects($this->once())
            ->method('getInstall')
            ->willReturn(null);
        $unInstalled->expects($this->exactly(2))
            ->method('getInstall')
            ->willReturn($installScript);

        $query->expects($this->once())
            ->method('patch')
            ->with($this->equalTo($installScript))
            ->willReturn([2 => null]);

        $dbMap->expects($this->once())
            ->method('applyInstall')
            ->with($this->equalTo($unInstalled));
        $fileMap->expects($this->once())
            ->method('applyInstall')
            ->with($this->equalTo($unInstalled));

        $installer = new Installer(
            $repository,
            $query,
            $fileHandle,
            $dbMap,
            $fileMap,
            $factory
        );

        $installer->installAll();
    }

    public function testUpdateInstallScriptsNoPatches()
    {

        $table = 'someTable';
        $tables = [$table];
        $repository = $this->createRepository();
        $query = $this->createQuery();
        $fileHandle = $this->createFileHandle();
        $dbMap = $this->createMap();
        $fileMap = $this->createMap();
        $factory = $this->createFactory();
        $lastPatch = $this->createPatch();
        $patchIterator = new \ArrayIterator([$lastPatch]);
        $patchCount = -1;
        $originalInstall = $this->createInstall();
        $patched = $this->createPatchCollection();
        $installScript = 'stuff';

        $installer = new Installer(
            $repository,
            $query,
            $fileHandle,
            $dbMap,
            $fileMap,
            $factory
        );

        $installer->updateInstallScripts($originalInstall, $patched, $patchCount);
    }

    public function testUpdateInstallScriptsPatches()
    {

        $table = 'someTable';
        $repository = $this->createRepository();
        $query = $this->createQuery();
        $fileHandle = $this->createFileHandle();
        $dbMap = $this->createMap();
        $fileMap = $this->createMap();
        $factory = $this->createFactory();
        $lastPatch = $this->createPatch();
        $patchIterator = new \ArrayIterator([$lastPatch]);
        $patchCount = 1;
        $originalInstall = $this->createInstall();
        $newInstall = $this->createInstall();
        $patched = $this->createPatchCollection();
        $postPatchInstall = $this->createInstall();
        $patchId = 4;
        $installScript = 'install stuff';

        $patched->expects($this->once())
            ->method('count')
            ->willReturn($patchCount);
        $patched->expects($this->once())
            ->method('getIterator')
            ->willReturn($patchIterator);

        $lastPatch->expects($this->once())
            ->method('getTable')
            ->willReturn($table);
        $lastPatch->expects($this->once())
            ->method('getPatch')
            ->willReturn($patchId);

        $repository->expects($this->once())
            ->method('buildInstallFromDatabase')
            ->with($this->equalTo($table))
            ->willReturn($postPatchInstall);

        $postPatchInstall->expects($this->once())
            ->method('getInstall')
            ->willReturn($installScript);

        $factory->expects($this->once())
            ->method('createInstall')
            ->with($this->equalTo(['table'=>$table, 'patch'=>$patchId, 'install'=>$installScript]))
            ->willReturn($newInstall);

        $fileMap->expects($this->once())
            ->method('updateInstall')
            ->with($this->equalTo($originalInstall), $this->equalTo($newInstall));
        
        $installer = new Installer(
            $repository,
            $query,
            $fileHandle,
            $dbMap,
            $fileMap,
            $factory
        );

        $installer->updateInstallScripts($originalInstall, $patched, $patchCount);
    }
}
