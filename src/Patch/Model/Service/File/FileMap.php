<?php
namespace TallTree\Roots\Patch\Model\Service\File;

use League\Flysystem\Filesystem;
use TallTree\Roots\Patch\Model\Service\Map;
use TallTree\Roots\Patch\Model\Patch;

class FileMap implements Map
{
    private $filesystem;
    private $dbDir;

    public function __construct(Filesystem $filesystem, $dbDir)
    {
        $this->filesystem = $filesystem;
        $this->dbDir = $dbDir;
    }

    public function getPatches($table)
    {
        $filepath = $this->dbDir . 'Patches/' . $table . '.json';
        $patches = json_decode($this->filesystem->read($filepath), true);
        foreach ($patches as $key => &$patch) {
            $this->fixKeys($patch);
            $patch['patch'] = $key;
            $patch['table'] = $table;
        }
        return $patches;
    }

    public function applyPatch(Patch $patch)
    {
        $table = $patch->getTable();
        $tablePatch = $patch->getPatch();
        $filepath = $this->dbDir . 'Patches/' . $table . '.json';
        $patches = json_decode($this->filesystem->read($filepath), true);
        $patches[$tablePatch] = $patch->dump();
        $encoded = json_encode($patches, JSON_PRETTY_PRINT);
        $this->filesystem->delete($filepath);
        $this->filesystem->write($filepath, $encoded);
    }

    public function updatePatch(Patch $originalPatch, Patch $newPatch)
    {
        $fields = array_diff_assoc($originalPatch->dump(), $newPatch->dump());
        $fields['id'] = $originalPatch->getId();
        $table = $originalPatch->getTable();
        $tablePatch = $originalPatch->getPatch();
        $filepath = $this->dbDir . 'Patches/' . $table . '.json';
        $patches = json_decode($this->filesystem->read($filepath), true);
        $currentPatch = $patches[$tablePatch];
        $mergedPatches = array_merge($currentPatch, $fields);
        $patches[$tablePatch] = $mergedPatches;
        $encoded = json_encode($patches, JSON_PRETTY_PRINT);
        $this->filesystem->delete($filepath);
        $this->filesystem->write($filepath, $encoded);
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
