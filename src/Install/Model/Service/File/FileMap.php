<?php
namespace TallTree\Roots\Install\Model\Service\File;

use League\Flysystem\Filesystem;
use TallTree\Roots\Install\Model\Service\Map;
use TallTree\Roots\Install\Model\Install;
use TallTree\Roots\Service\Transform\NameSpaces;

class FileMap implements Map
{
    private $filesystem;
    private $transformer;
    private $dbDir;
    private $dbOnlyFields = ['nameSpace'];

    public function __construct(Filesystem $filesystem, NameSpaces $transformer, $dbDir)
    {
        $this->filesystem = $filesystem;
        $this->transformer = $transformer;
        $this->dbDir = $dbDir;
    }

    public function getInstall($table)
    {
        $filepath = $this->loadFilepath($table);
        $installs = json_decode($this->filesystem->read($filepath), true);
        $install['nameSpace'] = $this->transformer->getAppNameSpace();
        $install['table'] = $table;
        return $installs;
    }

    public function applyInstall(Install $install)
    {
        $table = $install->getTable();
        $filepath = $this->loadFilepath($table);
        $fields = $install->dump();
        $fields = $this->removeDbOnlyFields($fields);
        $encoded = json_encode($fields, JSON_PRETTY_PRINT);
        $this->filesystem->delete($filepath);
        $this->filesystem->write($filepath, $encoded);
    }

    public function updateInstall(Install $originalInstall, Install $newInstall)
    {
        $table = $originalInstall->getTable();
        $filepath = $this->loadFilepath($table);
        $fields = $newInstall->dump();
        $fields = $this->removeDbOnlyFields($fields);
        $encoded = json_encode($fields, JSON_PRETTY_PRINT);
        $this->filesystem->delete($filepath);
        $this->filesystem->write($filepath, $encoded);
    }

    private function removeDbOnlyFields($fields = [])
    {
        foreach ($this->dbOnlyFields as $field) {
            unset($fields[$field]);
        }

        return $fields;
    }

    protected function loadFilepath($table)
    {
        $filepath = $this->dbDir . 'install/' . $table . '.json';

        return $filepath;
    }
}
