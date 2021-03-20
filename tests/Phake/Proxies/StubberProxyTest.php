<?php declare(strict_types=1);

namespace Phake\Proxies;

/*
 * Phake - Mocking Framework
 *
 * Copyright (c) 2010-2021, Mike Lively <m@digitalsandwich.com>
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *  *  Redistributions of source code must retain the above copyright
 *     notice, this list of conditions and the following disclaimer.
 *
 *  *  Redistributions in binary form must reproduce the above copyright
 *     notice, this list of conditions and the following disclaimer in
 *     the documentation and/or other materials provided with the
 *     distribution.
 *
 *  *  Neither the name of Mike Lively nor the names of his
 *     contributors may be used to endorse or promote products derived
 *     from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @category   Testing
 * @package    Phake
 * @author     Mike Lively <m@digitalsandwich.com>
 * @copyright  2010 Mike Lively <m@digitalsandwich.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link       http://www.digitalsandwich.com/
 */

use Phake;
use PHPUnit\Framework\TestCase;

/**
 * Description of StubberProxyTest
 *
 * @author Mike Lively <m@digitalsandwich.com>
 */
class StubberProxyTest extends TestCase
{
    /**
     * @var Phake\Proxies\StubberProxy
     */
    private $proxy;

    /**
     * @var Phake\IMock
     */
    private $stubbable;

    /**
     * Sets up test fixture
     */
    public function setUp(): void
    {
        $this->stubbable = Phake::mock(Phake\IMock::class);
        $this->proxy     = new StubberProxy($this->stubbable, new Phake\Matchers\Factory());
    }

    /**
     * Tests setting a stub on a method in the stubbable object
     */
    public function testCall()
    {
        $answerBinder = $this->proxy->foo();

        $this->assertThat($answerBinder, $this->isInstanceOf('Phake\Proxies\AnswerBinderProxy'));

        $this->phakeAssertAttributeInstanceOf('Phake\Stubber\AnswerBinder', 'binder', $answerBinder);
    }

    /**
     * Tests setting a stub on a static method in the stubbable object
     */
    public function testStaticCall()
    {
        $this->proxy     = new Phake\Proxies\StubberProxy(get_class($this->stubbable), new Phake\Matchers\Factory());
        $answerBinder = $this->proxy->foo();

        $this->assertThat($answerBinder, $this->isInstanceOf('Phake\Proxies\AnswerBinderProxy'));

        $this->phakeAssertAttributeInstanceOf('Phake\Stubber\AnswerBinder', 'binder', $answerBinder);
    }

    /**
     * Tests setting a stub with any parameters on a method in the stubbable object
     */
    public function testGet()
    {
        $answerBinder = $this->proxy->foo;

        $this->assertInstanceOf('Phake\Proxies\AnswerBinderProxy', $answerBinder);
        $this->phakeAssertAttributeInstanceOf('Phake\Stubber\AnswerBinder', 'binder', $answerBinder);
    }

    /**
     * Tests setting a stub with any parameters on a method in the stubbable object
     */
    public function testStaticGet()
    {
        $answerBinder = $this->proxy->foo;

        $this->assertInstanceOf('Phake\Proxies\AnswerBinderProxy', $answerBinder);
        $this->phakeAssertAttributeInstanceOf('Phake\Stubber\AnswerBinder', 'binder', $answerBinder);
    }

    public function testGetWithMatcher()
    {
        $mock = Phake::mock('PhakeTest_MagicClass');
        $proxy = new StubberProxy($mock, new Phake\Matchers\Factory());

        $answerBinder = $proxy->__get($this->anything());

        $this->assertInstanceOf('Phake\Proxies\AnswerBinderProxy', $answerBinder);
    }


    /**
     * @dataProvider magicGetInvalidData
     */
    public function testMagicGetWithInvalidData($invalidData, $exceptionContains)
    {
        $this->expectException('InvalidArgumentException', $exceptionContains);
        $this->proxy->__get($invalidData);
    }

    public function magicGetInvalidData()
    {
        return array(
            array('1foo', 'cannot start with an integer'),
            array(1,      'must be a string'),
        );
    }

    private function phakeAssertAttributeInstanceOf(string $class, string $propertyName, $object)
    {
         $reflectionObject = new \ReflectionObject($object);
         $reflectionProperty = $reflectionObject->getProperty($propertyName);
         $reflectionProperty->setAccessible(true);
         $value = $reflectionProperty->getValue($object);

         $this->assertInstanceOf($class, $value);
     }

}
