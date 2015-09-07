<?php
namespace TallTree\Roots\Install\Model\Service\Database;

use TallTree\Roots\Service\Database\Query;
use TallTree\Roots\Install\Model\Service\Map;
use TallTree\Roots\Install\Model\Install;

class MySqlMap implements Map
{
    const SELECT_PATCHES_TABLE = "SELECT `id`, `table` FROM `install` WHERE `table` = :table";
    const SHOW_CREATE_TABLE = "SHOW CREATE TABLE `%s`";
    const APPLY_PATCH = "INSERT INTO `install` SET %s";
    const UPDATE_PATCH = "UPDATE `install` SET %s WHERE id = :id";
    const SET_VALUE = "`%s` = :%s ";

    private $query;

    public function __construct(Query $query)
    {
        $this->query = $query;
    }

    public function getInstall($table)
    {
        $results = $this->query->read(static::SELECT_PATCHES_TABLE, ['table' => $table]);
        if (!empty($results)) {
            $results['install'] = $this->query->read(static::SHOW_CREATE_TABLE, []);
        } else {
            $results = ['table' => $table, 'install' => ''];
        }
        return $results; //$this->query->read(static::SELECT_PATCHES_TABLE, ['table' => $table]);
    }

    public function applyInstall(Install $install)
    {
        $fields = $install->dump();
        $query = sprintf(static::APPLY_PATCH, $this->buildSet($fields));
        $this->query->write($query, $fields);
    }

    public function updateInstall(Install $originalInstall, Install $newInstall)
    {
        $fields = array_diff_assoc($newInstall-> dump(), $originalInstall->dump());
        $fields['id'] = $originalInstall->getId();
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
