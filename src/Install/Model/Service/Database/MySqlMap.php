<?php
namespace TallTree\Roots\Install\Model\Service\Database;

use TallTree\Roots\Service\Database\Query;
use TallTree\Roots\Install\Model\Service\Map;
use TallTree\Roots\Install\Model\Install;
use TallTree\Roots\Service\Transform\NameSpaces;

class MySqlMap implements Map
{
    const SELECT_PATCHES_TABLE = "SELECT * FROM `install` WHERE `table` = :table";
    const SHOW_CREATE_TABLE = "SHOW CREATE TABLE `%s`";
    const SHOW_COLUMNS = "SHOW COLUMNS FROM `install`";
    const APPLY_PATCH = "INSERT INTO `install` SET %s";
    const UPDATE_PATCH = "UPDATE `install` SET %s WHERE id = :id";
    const SET_VALUE = "`%s` = :%s ";

    private $query;
    private $transformer;

    public function __construct(Query $query, NameSpaces $transformer)
    {
        $this->query = $query;
        $this->transformer = $transformer;
    }

    public function getInstall($table)
    {
        $results = $this->query->read(
            $this->transformer->addNameSpaceToQuery(static::SELECT_PATCHES_TABLE, false),
            ['table' => $table]
        );
        if (!empty($results)) {
            $results = $results[0];
            $install = $this->query->read(
                $this->transformer->addNameSpaceToQuery(sprintf(static::SHOW_CREATE_TABLE, $table)),
                []
            );
            if (!empty($install)) {
                $query = preg_replace(
                    '#AUTO_INCREMENT=\d+#',
                    'AUTO_INCREMENT=0',
                    $install[0]['Create Table']
                );
                $results['install'] = $this->transformer->removeNameSpaceFromQuery($query);
            }
        } else {
            $results = ['table' => $table, 'install' => ''];
        }
        return $results;
    }

    public function applyInstall(Install $install)
    {
        $fields = $install->dump();
        $fields['nameSpace'] = $this->transformer->getAppNameSpace();
        $fields = $this->reduceFields($fields);
        $query = sprintf(
            $this->transformer->addNameSpaceToQuery(static::APPLY_PATCH, false),
            $this->buildSet($fields)
        );
        $this->query->write($query, $fields);
    }

    public function updateInstall(Install $originalInstall, Install $newInstall)
    {
        $fields = array_diff_assoc($newInstall-> dump(), $originalInstall->dump());
        $fields['id'] = $originalInstall->getId();
        $fields['nameSpace'] = $this->transformer->getAppNameSpace();
        $fields = $this->reduceFields($fields);
        $query = sprintf(
            $this->transformer->addNameSpaceToQuery(static::UPDATE_PATCH, false),
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
