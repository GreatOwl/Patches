<?php
namespace GreatOwl\Patches;


use GreatOwl\Patches\Patch\Model\Service\Map;
use GreatOwl\Patches\Patch\Model\Service\File\FileMap;
use GreatOwl\Patches\Patch\Model\Patch;
use GreatOwl\Patches\Patch\Repository;
use GreatOwl\Patches\Service\Database\Query;
use GreatOwl\Patches\Service\File\Handle;

class Worker
{

    private $repository;
    private $query;
    private $fileHandle;
    private $dbMap;
    private $fileMap;

    public function __construct(Repository $repository, Query $query, Handle $fileHandle, Map $dbMap, FileMap $fileMap)
    {
        $this->repository = $repository;
        $this->query = $query;
        $this->fileHandle = $fileHandle;
        $this->dbMap = $dbMap;
        $this->fileMap = $fileMap;
    }

    public function patchTable($table)
    {
        $patched = $this->repository->buildPatchesFromDatabase($table);
        $unPatched = $this->repository->buildPatchesFromFile($table);

        $unPatched->diff($patched);

        /** @var Patch $patch */
        foreach ($unPatched as $patch) {
            $error = $this->query->patch($patch->getQuery());
            if (is_null($error[2])) {
                $this->dbMap->applyPatch($patch);
                $this->fileMap->applyPatch($patch);
            }
        }

    }

    public function patchAll()
    {
        $patchFiles = $this->fileHandle->getAllFilesInDir('Patches/');
        foreach ($patchFiles as $patchFile) {
            $this->patchTable($patchFile);
        }
    }
}
