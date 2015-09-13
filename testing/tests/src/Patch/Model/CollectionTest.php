<?php
namespace src\Patch\Model;

use TallTree\Roots\Patch\Model\Collection;

class CollectionTest extends \PHPUnit_Framework_TestCase
{

    public function thingProvider()
    {
        return [
            'things' => [['thing1', 'thing2', 'thing3']],
            'no things' => [[]]
        ];
    }

    /**
     * @dataProvider thingProvider
     * @param $patches
     */
    public function testGetIterator($patches)
    {
        $collection = new Collection($patches);

        $this->assertEquals(new \ArrayIterator($patches), $collection->getIterator());
    }

    /**
     * @dataProvider thingProvider
     * @param $patches
     */
    public function testCount($patches)
    {
        $collection = new Collection($patches);

        $this->assertEquals(count($patches), $collection->count());
    }

    /**
     * @dataProvider thingProvider
     * @param $patches
     */
    public function testFindAll($patches)
    {
        $collection = new Collection($patches);

        $this->assertInstanceOf(
            'TallTree\Roots\Patch\Model\Collection',
            $collection->findAll(function(){return true;})
        );
    }

    /**
     * @dataProvider thingProvider
     * @param $patches
     */
    public function testFind($patches)
    {
        $collection = new Collection($patches);

        $patch = null;
        if (array_key_exists(0, $patches)) {
            $patch = $patches[0];
        }

        $this->assertEquals(
            $patch,
            $collection->find(function(){return true;})
        );
    }
}
