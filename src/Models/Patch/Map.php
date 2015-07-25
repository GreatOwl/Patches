<?php
namespace GreatOwl\Patches\Models\Patch;


use GreatOwl\Patches\Models\Service\Database\Query;
use League\Flysystem\Filesystem;

class Map
{
    const SELECT_PATCHES_TABLE = "SELECT `id`, `table`, `patch`, `query`, `status`, `rollback` FROM patch WHERE `table` = :table AND `status` = true";
    const APPLY_PATCH = "INSERT INTO patch SET %s";
    const UPDATE_PATCH = "UPDATE patch SET %s WHERE id = :id";
    const SET_VALUE = "`%s` = :%s ";

    private $query;
    private $filesystem;
    private $dbDir;

    public function __construct(Query $query, Filesystem $filesystem, $dbDir)
    {
        $this->query = $query;
        $this->filesystem = $filesystem;
        $this->dbDir = $dbDir;
    }

    public function getPatchesFromDB($table)
    {
        return $this->query->read(static::SELECT_PATCHES_TABLE, ['table' => $table]);
    }

    public function getPatchesFromFile($table)
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

    public function getAllPatchFiles()
    {
        $filePath = $this->dbDir . 'Patches/';
        $files = $this->filesystem->listContents($filePath);
        $patches = [];
        foreach($files as $file) {
            if (strtolower($file['extension']) == "json") {
                $patches[] = $file['filename'];
            }
        }
        return $patches;
    }

    public function applyPatch(Patch $patch)
    {
        $fields = $patch->dump();
        $query = sprintf(static::APPLY_PATCH, $this->buildSet($fields));
        $this->query->write($query, $fields);
    }

    public function updatePatch(Patch $patch)
    {
        $fields = $patch->getChanges();
        $fields['id'] = $patch->getId();
        $query = sprintf(static::UPDATE_PATCH, $this->buildSet($fields));
        $this->query->write($query, $fields);
    }

    protected function buildSet($fields)
    {
        $query = '';
        $comma = false;
        foreach ($fields as $field => $value)
        {
            if ($comma) {
                $query .= ",";
            }
            $query .= sprintf(static::SET_VALUE, $field, $field);
            $comma = true;
        }
        return $query;
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
