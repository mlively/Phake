<?php
/*
 * Phake - Mocking Framework
 *
 * Copyright (c) 2010-2012, Mike Lively <m@digitalsandwich.com>
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

use PHPUnit\Framework\TestCase;

class Phake_Proxies_CallStubberProxyTest extends TestCase
{
    /**
     * @var Phake_Proxies_CallStubberProxy
     */
    private $proxy;

    /**
     * @var Phake_Matchers_IChainableArgumentMatcher
     */
    private $matcher1;

    /**
     * @var Phake_IMock
     */
    private $obj;

    /**
     * Sets up test fixture
     */
    public function setUp(): void
    {
        $this->matcher1 = Phake::mock('Phake_Matchers_IChainableArgumentMatcher');
        $this->obj      = $this->getMockBuilder('Phake_IMock')->getMock();
        PhakeTestUtil::setStubMapper($this->obj, Phake::mock('Phake_Stubber_StubMapper'));
        $this->proxy    = new Phake_Proxies_CallStubberProxy($this->matcher1, false);
    }

    /**
     * Tests setting a stub on a method in the stubbable object
     */
    public function testIsCalledOn()
    {
        $answerBinder = $this->proxy->isCalledOn($this->obj);

        $this->assertThat($answerBinder, $this->isInstanceOf('Phake_Proxies_AnswerBinderProxy'));

        $this->phakeAssertAttributeInstanceOf('Phake_Stubber_AnswerBinder', 'binder', $answerBinder);
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


