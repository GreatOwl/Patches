<?php
/**
 * @copyright ©2005—2015 Quicken Loans Inc. All rights reserved. Trade Secret, Confidential and Proprietary. Any
 *     dissemination outside of Quicken Loans is strictly prohibited.
 */

namespace src\Tools;

use TallTree\Roots\Tools\Container;

class ContainerTest extends \PHPUnit_Framework_TestCase
{

    public function testHas()
    {
        $data = ['key' => 'value'];

        $container = new Container($data);

        $this->assertTrue($container->has('key'));
        $this->assertFalse($container->has('something'));
    }

    public function testGet()
    {
        $data = ['key' => 'value'];

        $container = new Container($data);

        $this->assertEquals($data['key'], $container->get('key'));
        $this->assertNull($container->get('something'));
        $this->assertEquals(false, $container->get('something', false));
    }

    public function testSet()
    {
        $data = ['key' => 'value'];

        $container = new Container($data);

        $container = $container->set('something', 'some value');
        $this->assertInstanceOf('TallTree\Roots\Tools\Container', $container);
        $this->assertEquals('some value', $container->get('something'));
        $this->assertEquals('value', $container->get('key'));
    }

    public function testDump()
    {
        $data = ['key' => 'value'];

        $container = new Container($data);

        $this->assertEquals($data, $container->dump());
        $container = $container->set('something', 'some value');
        $this->assertInstanceOf('TallTree\Roots\Tools\Container', $container);

        $data['something'] = 'some value';

        $this->assertEquals($data, $container->dump());
    }
}
