<?php
namespace TallTree\Roots\Patch\Model\Service\Database;


use TallTree\Roots\Service\Database\Query;
use TallTree\Roots\Patch\Model\Service\Map;
use TallTree\Roots\Patch\Model\Patch;
use TallTree\Roots\Tools\NameSpaceLoader;

class MySqlMap implements Map
{
    use NameSpaceLoader;

    const SELECT_PATCHES_TABLE = "SELECT `id`, `table`, `patch`, `query`, `rollback` FROM `%s` WHERE `table` = :table";
    const APPLY_PATCH = "INSERT INTO `%s` SET %s";
    const UPDATE_PATCH = "UPDATE `%s` SET %s WHERE id = :id";
    const SET_VALUE = "`%s` = :%s ";
    const TABLE_ROOT = "%spatch";
    const TABLE_APP = "`%s%s`";
    const TABLE = "`%s`";

    private $query;

    public function __construct(Query $query, $namespaces = [])
    {
        $this->query = $query;
        $this->loadNameSpaces($namespaces);
    }

    public function getPatches($table)
    {
        return $this->query->read(
            sprintf(static::SELECT_PATCHES_TABLE, sprintf(static::TABLE_ROOT, $this->rootNamespace)),
            ['table' => $table]);
    }

    public function applyPatch(Patch $patch)
    {
        $fields = $patch->dump();
        $query = sprintf(
            static::APPLY_PATCH,
            sprintf(static::TABLE_ROOT, $this->rootNamespace),
            $this->buildSet($fields)
        );
        $this->query->write($query, $fields);
    }

    public function updatePatch(Patch $originalPatch, Patch $newPatch)
    {
        $fields = array_diff_assoc($newPatch-> dump(), $originalPatch->dump());
        $fields['id'] = $originalPatch->getId();
        $query = sprintf(
            static::UPDATE_PATCH,
            sprintf(static::TABLE_ROOT, $this->rootNamespace),
            $this->buildSet($fields)
        );
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
