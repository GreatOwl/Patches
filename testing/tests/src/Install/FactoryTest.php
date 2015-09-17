<?php
namespace TallTree\Roots\Install;

class FactoryTest extends \PHPUnit_Framework_TestCase
{

    public function testCreateInstall()
    {
        $raw = ['table'=>'someTable', 'install' => 'someInstall'];
        $factory = new Factory();

        $this->assertInstanceOf('TallTree\Roots\Install\Model\Install', $factory->createInstall($raw));
    }
}
