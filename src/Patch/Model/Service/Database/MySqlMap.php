<?php
namespace TallTree\Roots\Patch\Model\Service\Database;


use TallTree\Roots\Service\Database\Query;
use TallTree\Roots\Patch\Model\Service\Map;
use TallTree\Roots\Patch\Model\Patch;

class MySqlMap implements Map
{
    const SELECT_PATCHES_TABLE = "SELECT `id`, `table`, `patch`, `query`, `rollback` FROM patch WHERE `table` = :table AND `status` = true";
    const APPLY_PATCH = "INSERT INTO patch SET %s";
    const UPDATE_PATCH = "UPDATE patch SET %s WHERE id = :id";
    const SET_VALUE = "`%s` = :%s ";

    private $query;

    public function __construct(Query $query)
    {
        $this->query = $query;
    }

    public function getPatches($table)
    {
        return $this->query->read(static::SELECT_PATCHES_TABLE, ['table' => $table]);
    }

    public function applyPatch(Patch $patch)
    {
        $fields = $patch->dump();
        $query = sprintf(static::APPLY_PATCH, $this->buildSet($fields));
        $this->query->write($query, $fields);
    }

    public function updatePatch(Patch $originalPatch, Patch $newPatch)
    {
        $fields = array_diff_assoc($originalPatch->dump(), $newPatch->dump());
        $fields['id'] = $originalPatch->getId();
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
}
