<?php
namespace GreatOwl\Patches;


use GreatOwl\Patches\Models\Patch\Map;
use GreatOwl\Patches\Models\Patch\Patch;
use GreatOwl\Patches\Models\Patch\Repository;
use GreatOwl\Patches\Models\Service\Database\Query;

class Worker
{

    private $repository;
    private $query;
    private $patchMap;

    public function __construct(Repository $repository, Query $query, Map $patchMap)
    {
        $this->repository = $repository;
        $this->query = $query;
        $this->patchMap = $patchMap;
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
                $patch->setStatus(true);
            } else {
                $patch->setStatus(false);
            }
            $this->patchMap->applyPatch($patch);
        }

    }

    public function patchAll()
    {
        $patchFiles = $this->patchMap->getAllPatchFiles();
        foreach ($patchFiles as $patchFile) {
            $this->patchTable($patchFile);
        }
    }
}
