<?php
namespace TallTree\Roots\Install\Model\Service\File;

use League\Flysystem\Filesystem;
use TallTree\Roots\Install\Model\Service\Map;
use TallTree\Roots\Install\Model\Install;

class FileMap implements Map
{
    private $filesystem;
    private $dbDir;

    public function __construct(Filesystem $filesystem, $dbDir)
    {
        $this->filesystem = $filesystem;
        $this->dbDir = $dbDir;
    }

    public function getInstall($table)
    {
        $filepath = $this->loadFilepath($table);
        $installs = json_decode($this->filesystem->read($filepath), true);
        $install['table'] = $table;
        return $installs;
    }

    public function applyInstall(Install $install)
    {
        $table = $install->getTable();
        $filepath = $this->loadFilepath($table);
        $encoded = json_encode($install->dump(), JSON_PRETTY_PRINT);
        $this->filesystem->delete($filepath);
        $this->filesystem->write($filepath, $encoded);
    }

    public function updateInstall(Install $originalInstall, Install $newInstall)
    {
        $table = $originalInstall->getTable();
        $filepath = $this->loadFilepath($table);
        $encoded = json_encode($newInstall->dump(), JSON_PRETTY_PRINT);
        $this->filesystem->delete($filepath);
        $this->filesystem->write($filepath, $encoded);
    }

    protected function loadFilepath($table)
    {
        $filepath = $this->dbDir . 'install/' . $table . '.json';

        return $filepath;
    }
}
