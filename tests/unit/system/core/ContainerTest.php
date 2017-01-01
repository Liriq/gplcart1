<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\tests\unit\system\core;

use ReflectionException;
use gplcart\core\Container;
use gplcart\tests\resources\UnitTest;

/**
 * Test cases for DI container class
 */
class ContainerTest extends UnitTest
{
    /**
     * @covers gplcart\core\Container::instance
     * @group core
     */
    public function testInstance()
    {
        $passed = 0;
        $instance = Container::instance('gplcart\\core\\Facade');
        $passed += (int) is_object($instance);

        try {
            Container::instance('fake\\Class');
        } catch (ReflectionException $e) {
            $passed ++;
        }

        $this->assertEquals(2, $passed);
    }

    /**
     * @covers gplcart\core\Container::registered
     * @depends testInstance
     * @group core
     */
    public function testRegistered()
    {
        Container::instance('gplcart\\core\\Facade');
        $instance = Container::registered('gplcart\\core\\Facade');
        $this->assertTrue(is_object($instance));
    }

    /**
     * @covers gplcart\core\Container::register
     * @depends testRegistered
     * @group core
     */
    public function testRegister()
    {
        Container::register('test\\Test', (object) array());
        $instance = Container::registered('test\\Test');
        $this->assertTrue(is_object($instance));
    }

    /**
     * @covers gplcart\core\Container::unregister
     * @depends testRegister
     * @group core
     */
    public function testUnregister()
    {
        Container::register('test\\Test', (object) array());
        Container::unregister('test\\Test');
        $instance = Container::registered('test\\Test');
        $this->assertTrue(empty($instance));
    }

}
