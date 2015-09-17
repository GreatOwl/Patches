<?php
namespace TallTree\Roots\Install\Model\Service\Database;

use TallTree\Roots\Service\Database\Query;
use TallTree\Roots\Install\Model\Service\Map;
use TallTree\Roots\Install\Model\Install;
use TallTree\Roots\Tools\NameSpaceLoader;

class MySqlMap implements Map
{
    use NameSpaceLoader;

    const SELECT_PATCHES_TABLE = "SELECT `id`, `table`, `install`, `patch` FROM `%s` WHERE `table` = :table";
    const SHOW_CREATE_TABLE = "SHOW CREATE TABLE `%s`";
    const APPLY_PATCH = "INSERT INTO `%s` SET %s";
    const UPDATE_PATCH = "UPDATE `%s` SET %s WHERE id = :id";
    const SET_VALUE = "`%s` = :%s ";
    const TABLE_ROOT = "%sinstall";
    const TABLE_APP = "`%s%s`";
    const TABLE = "`%s`";

    private $query;

    public function __construct(Query $query, $namespaces = [])
    {
        $this->query = $query;
        $this->loadNameSpaces($namespaces);
    }

    public function getInstall($table)
    {
        $results = $this->query->read(
            sprintf(static::SELECT_PATCHES_TABLE, sprintf(static::TABLE_ROOT, $this->rootNamespace)),
            ['table' => $this->appNamespace . $table]
        );
        if (!empty($results)) {
            $results = $results[0];
            $install = $this->query->read(sprintf(static::SHOW_CREATE_TABLE, $this->appNamespace . $table), []);
            if (!empty($install)) {
                $query = preg_replace(
                    '#AUTO_INCREMENT=\d+#',
                    'AUTO_INCREMENT=0',
                    $install[0]['Create Table']
                );
                $query = str_replace(
                    'FROM ' . sprintf(static::TABLE_APP, $this->appNamespace, $table),
                    'FROM ' . sprintf(static::TABLE, $table),
                    $query
                );
                $results['install'] = $query;
            }
        } else {
            $results = ['table' => $table, 'install' => ''];
        }
        return $results;
    }

    public function applyInstall(Install $install)
    {
        $fields = $install->dump();
        $query = sprintf(
            static::APPLY_PATCH,
            sprintf(static::TABLE_ROOT, $this->rootNamespace),
            $this->buildSet($fields)
        );
        $this->query->write($query, $fields);
    }

    public function updateInstall(Install $originalInstall, Install $newInstall)
    {
        $fields = array_diff_assoc($newInstall-> dump(), $originalInstall->dump());
        $fields['id'] = $originalInstall->getId();
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
