<?php
namespace TallTree\Roots\Patch;


class FactoryTest extends \PHPUnit_Framework_TestCase
{

    public function createPatch()
    {
        $patch = $this->getMockBuilder('TallTree\Roots\Patch\Model\Patch')
            ->disableOriginalConstructor()
            ->getMock();
        return $patch;
    }

    public function testCreatePatch()
    {
        $raw = [
            'query' => 'someQuery',
            'rollback' => 'someRollback',
            'patch' => 'somePatch',
            'table' => 'someTable'
        ];

        $factory = new Factory();
        $this->assertInstanceOf('TallTree\Roots\Patch\Model\Patch', $factory->createPatch($raw));
    }

    public function testCreateCollection()
    {
        $patches = [
            $this->createPatch(),
            $this->createPatch(),
            $this->createPatch(),
            $this->createPatch()
        ];

        $factory = new Factory();
        $this->assertInstanceOf('TallTree\Roots\Patch\Model\Collection', $factory->createCollection($patches));
    }
}
