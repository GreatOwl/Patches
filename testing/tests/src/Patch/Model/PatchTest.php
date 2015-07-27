<?php
namespace src\Patch\Model;

use TallTree\Roots\Patch\Model\Patch;

class PatchTest extends \PHPUnit_Framework_TestCase
{

    public function testCreationValidationSucces()
    {
        $raw = [
            'table' => 'someTable',
            'patch' => 'somePatch',
            'query' => 'someQuery',
            'rollback' => 'someRollback'
        ];

        $patch = new Patch($raw);

        $this->assertInstanceOf('TallTree\Roots\Patch\Model\Patch', $patch);
    }

    public function testGets()
    {
        $raw = [
            'table' => 'someTable',
            'patch' => 'somePatch',
            'query' => 'someQuery',
            'rollback' => 'someRollback'
        ];

        $patch = new Patch($raw);

        $this->assertEquals(null, $patch->getId());
        $this->assertEquals(1234, $patch->getId(1234));
        $this->assertEquals($raw['table'], $patch->getTable());
        $this->assertEquals($raw['patch'], $patch->getPatch());
        $this->assertEquals($raw['query'], $patch->getQuery());
        $this->assertEquals($raw['rollback'], $patch->getRollback());
        $this->assertEquals($raw, $patch->dump());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage patch is a required parameter.
     */
    public function testCreationValidationThrowsException()
    {
        $raw = [
            'table' => 'someTable',
            'query' => 'someQuery',
            'rollback' => 'someRollback'
        ];

        $patch = new Patch($raw);
    }
}
