<?php
namespace TallTree\Roots\Install;

use TallTree\Roots\Install\Model\Collection;
use TallTree\Roots\Install\Model\Service\Map;

class Repository
{
    /**
     * @var Map $fileMap
     */
    private $fileMap;

    /**
     * @var Map $dbMap
     */
    private $dbMap;

    /**
     * @var Factory $factory
     */
    private $factory;

    public function __construct(Map $dbMap, Map $fileMap, Factory $factory)
    {
        $this->fileMap = $fileMap;
        $this->dbMap = $dbMap;
        $this->factory = $factory;
    }

    public function buildInstallFromDatabase($table)
    {
        $rawInstall = $this->dbMap->getInstall($table);
        if (!is_null($rawInstall)) {
            return $this->factory->createInstall($rawInstall);//$this->buildInstall($rawInstalls);
        } else {
            return null;
        }
    }

    public function buildInstallFromFile($table)
    {
        $rawInstall = $this->fileMap->getInstall($table);
        if (!is_null($rawInstall)) {
            return $this->factory->createInstall($rawInstall);//$this->buildInstall($rawInstalls);
        } else {
            return null;
        }
    }
}
