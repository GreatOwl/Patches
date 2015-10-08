<?php
namespace TallTree\Roots\Patch;

class PatcherTest extends \PHPUnit_Framework_TestCase
{

    private function createRepository()
    {
        $repository = $this->getMockBuilder('TallTree\Roots\Patch\Repository')
            ->disableOriginalConstructor()
            ->getMock();
        return $repository;
    }

    private function createFilterFactory()
    {
        $filterFactory = $this->getMockBuilder('TallTree\Roots\Patch\FilterFactory')
            ->disableOriginalConstructor()
            ->getMock();
        return $filterFactory;
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

    private function createInstallRepository()
    {
        $installRepository = $this->getMockBuilder('TallTree\Roots\Install\Repository')
            ->disableOriginalConstructor()
            ->getMock();
        return $installRepository;
    }

    private function createInstaller()
    {
        $installer = $this->getMockBuilder('TallTree\Roots\Install\Installer')
            ->disableOriginalConstructor()
            ->getMock();
        return $installer;
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

    private function createCallable() {
        return function() {

        };
    }

    public function testPatchTablePatchErrors()
    {
        $repo = $this->createRepository();
        $query = $this->createQuery();
        $handle = $this->createFileHandle();
        $fileMap = $this->createMap();
        $dbMap = $this->createMap();
        $filterFactory = $this->createFilterFactory();
        $installRepository = $this->createInstallRepository();
        $installer = $this->createInstaller();
        $install = $this->createInstall();
        $nameSpaces = $this->createNameSpacesTransform();

        $table = 'someTable';
        $queryString = 'someQuery';
        $error = ['something 0', 'something 1', 'something 2'];

        $dbCollection = $this->createCollection();
        $fileCollection = $this->createCollection();

        $repo->expects($this->exactly(2))
            ->method('buildPatchesFromDatabase')
            ->with($this->equalTo($table))
            ->will($this->returnValue($dbCollection));
        $repo->expects($this->once())
            ->method('buildPatchesFromFile')
            ->with($this->equalTo($table))
            ->will($this->returnValue($fileCollection));

        $installRepository->expects($this->once())
            ->method('buildInstallFromFile')
            ->with($this->equalTo($table))
            ->will($this->returnValue($install));

        $findUnmatched = $this->createCallable();
        $filterFactory->expects($this->once())
            ->method('findUnmatched')
            ->with($this->equalTo($dbCollection))
            ->will($this->returnValue($findUnmatched));
        $findAfterInstall = $this->createCallable();
        $filterFactory->expects($this->once())
            ->method('findAfterInstall')
            ->with($this->equalTo($install))
            ->will($this->returnValue($findAfterInstall));

        $unmatched = $this->createCollection();
        $fileCollection->expects($this->once())
            ->method('findAll')
            ->with($this->equalTo($findUnmatched))
            ->will($this->returnValue($unmatched));

        $unmatchedAfterInstall = $this->createCollection();
        $unmatched->expects($this->once())
            ->method('findAll')
            ->with($this->equalTo($findAfterInstall))
            ->will($this->returnValue($unmatchedAfterInstall));

        $patch = $this->createPatch();
        $patch->expects($this->once())
            ->method('getQuery')
            ->will($this->returnValue($queryString));

        $query->expects($this->once())
            ->method('patch')
            ->with($this->equalTo($queryString))
            ->will($this->returnValue($error));

        $unmatchedAfterInstall->expects($this->once())
            ->method('getIterator')
            ->will($this->returnValue(new \ArrayIterator([$patch])));

        $installer->expects($this->once())
            ->method('updateInstallScripts')
            ->with($this->equalTo($install), $this->equalTo($dbCollection));

        $controller = new Patcher(
            $repo,
            $filterFactory,
            $query,
            $handle,
            $dbMap,
            $fileMap,
            $installRepository,
            $installer,
            $nameSpaces
        );

        $controller->patchTable($table);
    }

    public function testPatchTablePatchNoErrors()
    {
        $repo = $this->createRepository();
        $query = $this->createQuery();
        $handle = $this->createFileHandle();
        $fileMap = $this->createMap();
        $dbMap = $this->createMap();
        $filterFactory = $this->createFilterFactory();
        $installRepository = $this->createInstallRepository();
        $installer = $this->createInstaller();
        $install = $this->createInstall();
        $nameSpaces = $this->createNameSpacesTransform();

        $table = 'someTable';
        $queryString = 'someQuery';
        $error = ['something 0', 'something 1', null];

        $dbCollection = $this->createCollection();
        $fileCollection = $this->createCollection();

        $repo->expects($this->exactly(2))
            ->method('buildPatchesFromDatabase')
            ->with($this->equalTo($table))
            ->will($this->returnValue($dbCollection));
        $repo->expects($this->once())
            ->method('buildPatchesFromFile')
            ->with($this->equalTo($table))
            ->will($this->returnValue($fileCollection));

        $installRepository->expects($this->once())
            ->method('buildInstallFromFile')
            ->with($this->equalTo($table))
            ->will($this->returnValue($install));

        $findUnmatched = $this->createCallable();
        $filterFactory->expects($this->once())
            ->method('findUnmatched')
            ->with($this->equalTo($dbCollection))
            ->will($this->returnValue($findUnmatched));
        $findAfterInstall = $this->createCallable();
        $filterFactory->expects($this->once())
            ->method('findAfterInstall')
            ->with($this->equalTo($install))
            ->will($this->returnValue($findAfterInstall));

        $unmatched = $this->createCollection();
        $fileCollection->expects($this->once())
            ->method('findAll')
            ->with($this->equalTo($findUnmatched))
            ->will($this->returnValue($unmatched));

        $unmatchedAfterInstall = $this->createCollection();
        $unmatched->expects($this->once())
            ->method('findAll')
            ->with($this->equalTo($findAfterInstall))
            ->will($this->returnValue($unmatchedAfterInstall));

        $patch = $this->createPatch();
        $patch->expects($this->once())
            ->method('getQuery')
            ->will($this->returnValue($queryString));

        $query->expects($this->once())
            ->method('patch')
            ->with($this->equalTo($queryString))
            ->will($this->returnValue($error));

        $unmatchedAfterInstall->expects($this->once())
            ->method('getIterator')
            ->will($this->returnValue(new \ArrayIterator([$patch])));

        $dbMap->expects($this->once())
            ->method('applyPatch')
            ->with($this->equalTo($patch));
        $fileMap->expects($this->once())
            ->method('applyPatch')
            ->with($this->equalTo($patch));

        $installer->expects($this->once())
            ->method('updateInstallScripts')
            ->with($this->equalTo($install), $this->equalTo($dbCollection));

        $controller = new Patcher(
            $repo,
            $filterFactory,
            $query,
            $handle,
            $dbMap,
            $fileMap,
            $installRepository,
            $installer,
            $nameSpaces
        );;

        $controller->patchTable($table);
    }

    public function testPatchTablePatchAll()
    {
        $repo = $this->createRepository();
        $query = $this->createQuery();
        $handle = $this->createFileHandle();
        $fileMap = $this->createMap();
        $dbMap = $this->createMap();
        $filterFactory = $this->createFilterFactory();
        $installRepository = $this->createInstallRepository();
        $installer = $this->createInstaller();
        $install = $this->createInstall();
        $nameSpaces = $this->createNameSpacesTransform();

        $table = 'someTable';
        $queryString = 'someQuery';
        $error = ['something 0', 'something 1', null];

        $dbCollection = $this->createCollection();
        $fileCollection = $this->createCollection();

        $handle->expects($this->once())
            ->method('getAllFilesInDir')
            ->will($this->returnValue([$table]));

        $repo->expects($this->exactly(2))
            ->method('buildPatchesFromDatabase')
            ->with($this->equalTo($table))
            ->will($this->returnValue($dbCollection));
        $repo->expects($this->once())
            ->method('buildPatchesFromFile')
            ->with($this->equalTo($table))
            ->will($this->returnValue($fileCollection));

        $installRepository->expects($this->once())
            ->method('buildInstallFromFile')
            ->with($this->equalTo($table))
            ->will($this->returnValue($install));

        $findUnmatched = $this->createCallable();
        $filterFactory->expects($this->once())
            ->method('findUnmatched')
            ->with($this->equalTo($dbCollection))
            ->will($this->returnValue($findUnmatched));
        $findAfterInstall = $this->createCallable();
        $filterFactory->expects($this->once())
            ->method('findAfterInstall')
            ->with($this->equalTo($install))
            ->will($this->returnValue($findAfterInstall));

        $unmatched = $this->createCollection();
        $fileCollection->expects($this->once())
            ->method('findAll')
            ->with($this->equalTo($findUnmatched))
            ->will($this->returnValue($unmatched));

        $unmatchedAfterInstall = $this->createCollection();
        $unmatched->expects($this->once())
            ->method('findAll')
            ->with($this->equalTo($findAfterInstall))
            ->will($this->returnValue($unmatchedAfterInstall));

        $patch = $this->createPatch();
        $patch->expects($this->once())
            ->method('getQuery')
            ->will($this->returnValue($queryString));

        $query->expects($this->once())
            ->method('patch')
            ->with($this->equalTo($queryString))
            ->will($this->returnValue($error));

        $unmatchedAfterInstall->expects($this->once())
            ->method('getIterator')
            ->will($this->returnValue(new \ArrayIterator([$patch])));

        $dbMap->expects($this->once())
            ->method('applyPatch')
            ->with($this->equalTo($patch));
        $fileMap->expects($this->once())
            ->method('applyPatch')
            ->with($this->equalTo($patch));

        $installer->expects($this->once())
            ->method('updateInstallScripts')
            ->with($this->equalTo($install), $this->equalTo($dbCollection));

        $controller = new Patcher(
            $repo,
            $filterFactory,
            $query,
            $handle,
            $dbMap,
            $fileMap,
            $installRepository,
            $installer,
            $nameSpaces
        );

        $controller->patchAll();
    }
}
