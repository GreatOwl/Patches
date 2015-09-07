<?php
namespace TallTree\Roots\Install;


use TallTree\Roots\Install\Model\Service\Map;
use TallTree\Roots\Service\Database\Query;
use TallTree\Roots\Service\File\Handle;

class Handler
{

    private $repository;
    private $query;
    private $fileHandle;
    private $dbMap;
    private $fileMap;

    public function __construct(Repository $repository, Query $query, Handle $fileHandle, Map $dbMap, Map $fileMap)
    {
        $this->repository = $repository;
        $this->query = $query;
        $this->fileHandle = $fileHandle;
        $this->dbMap = $dbMap;
        $this->fileMap = $fileMap;
    }

    public function installTable($table)
    {
        $installed = $this->repository->buildInstallFromDatabase($table);
        $unInstalled = $this->repository->buildInstallFromFile($table);
        if (empty($installed->getInstall()) && !empty($unInstalled->getInstall())) {
            $error = $this->query->patch($unInstalled->getInstall());
            if (is_null($error[2])) {
                $this->dbMap->applyInstall($unInstalled);
                $this->fileMap->applyInstall($unInstalled);
            }
        }
    }

    public function installAll()
    {
        $installFiles = $this->fileHandle->getAllFilesInDir('install/');
        foreach ($installFiles as $installFile) {
            $this->installTable($installFile);
        }
    }
}
