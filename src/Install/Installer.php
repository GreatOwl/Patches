<?php
namespace TallTree\Roots\Install;


use TallTree\Roots\Install\Model\Install;
use TallTree\Roots\Install\Model\Service\Map;
use TallTree\Roots\Patch\Model\Collection;
use TallTree\Roots\Patch\Model\Patch;
use TallTree\Roots\Service\Database\Query;
use TallTree\Roots\Service\File\Handle;

class Installer
{

    private $repository;
    private $query;
    private $fileHandle;
    private $dbMap;
    private $fileMap;
    private $factory;

    public function __construct(
        Repository $repository,
        Query $query,
        Handle $fileHandle,
        Map $dbMap,
        Map $fileMap,
        Factory $factory
    ) {
        $this->repository = $repository;
        $this->query = $query;
        $this->fileHandle = $fileHandle;
        $this->dbMap = $dbMap;
        $this->fileMap = $fileMap;
        $this->factory = $factory;
    }

    public function installTable($table)
    {
        $installed = $this->repository->buildInstallFromDatabase($table);
        $unInstalled = $this->repository->buildInstallFromFile($table);
        if (empty($installed->getInstall()) && !empty($unInstalled->getInstall())) {
            $error = $this->query->patch($unInstalled->getInstall());
            if (is_null($error[2])) {
                $this->dbMap->applyInstall($unInstalled);
                $this->fileMap->applyInstall($unInstalled);
            }
        }
    }

    public function updateInstallScripts(Install $originalInstall, Collection $patched, $patchCount)
    {
        if ($patchCount >= 0) {
            $last = $patched->count() - 1;
            $patched = $patched->getIterator();
            /** @var Patch $lastPatch */
            $lastPatch = $patched->offsetGet($last);
            $table = $lastPatch->getTable();
            $postPatchInstall = $this->repository->buildInstallFromDatabase($table);
            $newInstall = $this->factory->createInstall([
                'table' => $table,
                'patch' => $lastPatch->getPatch(),
                'install' => $postPatchInstall->getInstall()
            ]);
            $this->fileMap->updateInstall($originalInstall, $newInstall);
        }
    }

    public function installAll()
    {
        $installFiles = $this->fileHandle->getAllFilesInDir('install/');
        foreach ($installFiles as $installFile) {
            $this->installTable($installFile);
        }
    }
}
