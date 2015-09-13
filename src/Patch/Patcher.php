<?php
namespace TallTree\Roots\Patch;

use TallTree\Roots\Install\Factory as InstallFactory;
use TallTree\Roots\Install\Installer;
use TallTree\Roots\Patch\Model\Service\Map;
use TallTree\Roots\Patch\Model\Patch;
use TallTree\Roots\Service\Database\Query;
use TallTree\Roots\Service\File\Handle;
use TallTree\Roots\Install\Repository as InstallRepository;
use TallTree\Roots\Install\Model\Service\Map as InstallMap;

class Patcher
{

    private $repository;
    private $installRepository;
    private $filterFactory;
    private $query;
    private $fileHandle;
    private $dbMap;
    private $fileMap;
    private $installer;

    public function __construct(
        Repository $repository,
        FilterFactory $filterFactory,
        Query $query,
        Handle $fileHandle,
        Map $dbMap,
        Map $fileMap,
        InstallRepository $installRepository,
        Installer $installer
    ) {
        $this->repository = $repository;
        $this->installRepository = $installRepository;
        $this->filterFactory = $filterFactory;
        $this->query = $query;
        $this->fileHandle = $fileHandle;
        $this->dbMap = $dbMap;
        $this->fileMap = $fileMap;
        $this->installer= $installer;
    }

    public function patchTable($table)
    {
        $patched = $this->repository->buildPatchesFromDatabase($table);
        $unPatched = $this->repository->buildPatchesFromFile($table);
        $originalInstall = $this->installRepository->buildInstallFromFile($table);

        $findUnmatched = $this->filterFactory->findUnmatched($patched);
        $findAfterInstall = $this->filterFactory->findAfterInstall($originalInstall);

        $unmatched  = $unPatched->findAll($findUnmatched);
        $unmatchedAfterInstall = $unmatched->findAll($findAfterInstall);

        /** @var Patch $patch */
        foreach ($unmatchedAfterInstall as $patch) {
            $error = $this->query->patch($patch->getQuery());
            if (is_null($error[2])) {
                $this->dbMap->applyPatch($patch);
                $this->fileMap->applyPatch($patch);
            }
        }

        $lastCount = $unmatchedAfterInstall->count() - 1;
        $patched = $this->repository->buildPatchesFromDatabase($table);
        $this->installer->updateInstallScripts($originalInstall, $patched, $lastCount);
    }

    public function patchAll()
    {
        $patchFiles = $this->fileHandle->getAllFilesInDir('Patches/');
        foreach ($patchFiles as $patchFile) {
            $this->patchTable($patchFile);
        }
    }
}
