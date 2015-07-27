<?php
namespace TallTree\Roots\Patch;

use TallTree\Roots\Patch\Model\Collection;
use TallTree\Roots\Patch\Model\Service\Map;

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

    public function buildPatchesFromDatabase($table)
    {
        $rawPatches = $this->dbMap->getPatches($table);

        return $this->buildPatches($rawPatches);

    }

    public function buildPatchesFromFile($table)
    {
        $rawPatches = $this->fileMap->getPatches($table);

        return $this->buildPatches($rawPatches);
    }

    /**
     * @param $rawPatches
     * @return Collection
     */
    protected function buildPatches($rawPatches)
    {
        $patches = [];
        foreach ($rawPatches as $patch) {
            $patches[] = $this->factory->createPatch($patch);
        }

        return $this->factory->createCollection($patches);
    }
}
