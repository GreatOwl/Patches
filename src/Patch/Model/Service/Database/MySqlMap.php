<?php
namespace TallTree\Roots\Patch\Model\Service\Database;


use TallTree\Roots\Service\Database\Query;
use TallTree\Roots\Patch\Model\Service\Map;
use TallTree\Roots\Patch\Model\Patch;
use TallTree\Roots\Service\Transform\NameSpaces;

class MySqlMap implements Map
{
    const SELECT_PATCHES_TABLE = "SELECT * FROM `patch` WHERE `table` = :table";
    const SHOW_COLUMNS = "SHOW COLUMNS FROM `patch`";
    const APPLY_PATCH = "INSERT INTO `patch` SET %s";
    const UPDATE_PATCH = "UPDATE `patch` SET %s WHERE id = :id";
    const SET_VALUE = "`%s` = :%s ";

    private $query;
    private $transformer;

    public function __construct(Query $query, NameSpaces $transformer)
    {
        $this->query = $query;
        $this->transformer = $transformer;
    }

    public function getPatches($table)
    {
        return $this->query->read(
            $this->transformer->addNameSpaceToQuery(static::SELECT_PATCHES_TABLE, false),
            ['table' => $table]);
    }

    public function applyPatch(Patch $patch)
    {
        $fields = $patch->dump();
        $fields['query'] = $this->transformer->removeNameSpaceFromQuery($fields['query']);
        $fields['rollback'] = $this->transformer->removeNameSpaceFromQuery($fields['rollback']);
        $fields['nameSpace'] = $this->transformer->getAppNameSpace();
        $fields = $this->reduceFields($fields);
        $query = sprintf(
            $this->transformer->addNameSpaceToQuery(static::APPLY_PATCH, false),
            $this->buildSet($fields)
        );
        $this->query->write($query, $fields);
    }

    public function updatePatch(Patch $originalPatch, Patch $newPatch)
    {
        $fields = array_diff_assoc($newPatch-> dump(), $originalPatch->dump());
        $fields['id'] = $originalPatch->getId();
        $fields['query'] = $this->transformer->removeNameSpaceFromQuery($fields['query']);
        $fields['rollback'] = $this->transformer->removeNameSpaceFromQuery($fields['rollback']);
        $fields['nameSpace'] = $this->transformer->getAppNameSpace();
        $fields = $this->reduceFields($fields);
        $query = sprintf(
            $this->transformer->addNameSpaceToQuery(static::UPDATE_PATCH,false),
            $this->buildSet($fields)
        );
        $this->query->write($query, $fields);
    }

    private function reduceFields($fields)
    {
        $columns = $this->loadColumns();
        return array_intersect_key($fields, $columns);
    }

    private function loadColumns()
    {
        $columns = [];
        $results = $this->query->read($this->transformer->addNameSpaceToQuery(static::SHOW_COLUMNS, false), []);
        foreach ($results as $column) {
            $columns[$column['Field']] = $column['Type'];
        }
        return $columns;
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
