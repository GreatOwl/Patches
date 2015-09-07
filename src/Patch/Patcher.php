<?php
namespace TallTree\Roots\Patch;


use TallTree\Roots\Patch\Model\Service\Map;
use TallTree\Roots\Patch\Model\Patch;
use TallTree\Roots\Service\Database\Query;
use TallTree\Roots\Service\File\Handle;

class Patcher
{

    private $repository;
    private $query;
    private $fileHandle;
    private $dbMap;
    private $fileMap;

    public function __construct(Repository $repository, Query $query, Handle $fileHandle, Map $dbMap, Map $fileMap)
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