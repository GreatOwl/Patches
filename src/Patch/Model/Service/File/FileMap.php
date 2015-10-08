<?php
namespace TallTree\Roots\Patch\Model\Service\File;

use League\Flysystem\Filesystem;
use TallTree\Roots\Patch\Model\Service\Map;
use TallTree\Roots\Patch\Model\Patch;
use TallTree\Roots\Service\Transform\NameSpaces;

class FileMap implements Map
{
    private $filesystem;
    private $dbDir;
    private $transformer;
    private $dbOnlyFields = ['nameSpace'];

    public function __construct(Filesystem $filesystem, NameSpaces $transformer, $dbDir)
    {
        $this->filesystem = $filesystem;
        $this->transformer = $transformer;
        $this->dbDir = $dbDir;
    }

    public function getPatches($table)
    {
        list($patches) = $this->loadPatches($table);
        foreach ($patches as $key => &$patch) {
            $this->fixKeys($patch);
            $patch['patch'] = $key;
            $patch['table'] = $table;
            $patch['query'] = $this->transformer->removeNameSpaceFromQuery($patch['query']);
            $patch['rollback'] = $this->transformer->removeNameSpaceFromQuery($patch['rollback']);
            $patch['nameSpace'] = $this->transformer->getAppNameSpace();
        }
        return $patches;
    }

    public function applyPatch(Patch $patch)
    {
        $table = $patch->getTable();
        $tablePatch = $patch->getPatch();
        list($patches, $filepath) = $this->loadPatches($table);
        $patches[$tablePatch] = $patch->dump();
        $patches = $this->removeDbOnlyFields($patches);
        $encoded = json_encode($patches, JSON_PRETTY_PRINT);
        $this->filesystem->delete($filepath);
        $this->filesystem->write($filepath, $encoded);
    }

    public function updatePatch(Patch $originalPatch, Patch $newPatch)
    {
        $fields = array_diff_assoc($newPatch->dump(), $originalPatch->dump());
        $fields['id'] = $originalPatch->getId();
        $table = $originalPatch->getTable();
        $tablePatch = $originalPatch->getPatch();
        list($patches, $filepath) = $this->loadPatches($table);
        $currentPatch = $patches[$tablePatch];
        $mergedPatches = array_merge($currentPatch, $fields);
        $patches[$tablePatch] = $mergedPatches;
        $patches = $this->removeDbOnlyFields($patches);
        $encoded = json_encode($patches, JSON_PRETTY_PRINT);
        $this->filesystem->delete($filepath);
        $this->filesystem->write($filepath, $encoded);
    }

    protected function loadPatches($table)
    {
        $filepath = $this->dbDir . 'Patches/' . $table . '.json';
        $patches = json_decode($this->filesystem->read($filepath), true);

        return [$patches, $filepath];
    }

    private function removeDbOnlyFields($patches = [])
    {
        foreach ($patches as &$patch) {
            foreach ($this->dbOnlyFields as $field) {
                unset($patch[$field]);
            }
        }

        return $patches;
    }

    private function fixKeys($params)
    {
        $keys = array_keys($params);
        $original = $keys;
        array_map('strtolower', $keys);
        foreach ($original as $key => $value) {
            $newKey = $keys[$key];
            unset($params[$key]);
            $params[$newKey];
        }
        return $params;
    }
}
