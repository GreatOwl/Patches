<?php
namespace src\Patch\Model;

use TallTree\Roots\Install\Model\Install;

class InstallTest extends \PHPUnit_Framework_TestCase
{

    public function testCreationValidationSucces()
    {
        $raw = [
            'table' => 'someTable',
            'patch' => 'somePatch',
            'install' => 'someInstall'
        ];

        $install = new Install($raw);

        $this->assertInstanceOf('TallTree\Roots\Install\Model\Install', $install);
    }

    public function testGets()
    {
        $raw = [
            'table' => 'someTable',
            'patch' => 'somePatch',
            'install' => 'someInstall'
        ];

        $install = new Install($raw);

        $this->assertEquals(null, $install->getId());
        $this->assertEquals(1234, $install->getId(1234));
        $this->assertEquals($raw['table'], $install->getTable());
        $this->assertEquals($raw['patch'], $install->getPatch());
        $this->assertEquals($raw['install'], $install->getInstall());
        $this->assertEquals($raw, $install->dump());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage table is a required parameter.
     */
    public function testCreationValidationThrowsException()
    {
        $raw = [
            'patch' => 'somePatch',
            'install' => 'someInstall'
        ];

        $install = new Install($raw);
    }
}
