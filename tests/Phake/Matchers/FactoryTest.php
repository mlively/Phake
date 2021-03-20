<?php declare(strict_types=1);

namespace Phake\Matchers;

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
use PHPUnit\Framework\Constraint\Constraint;

class FactoryTest extends TestCase
{
    /**
     * @var Factory
     */
    private $factory;

    /**
     * Sets up the test fixture
     */
    public function setUp(): void
    {
        $this->factory = new Factory();
    }

    /**
     * Tests creating a default matcher
     */
    public function testDefaultMatcher()
    {
        $matcher = $this->factory->createMatcher('foo');

        $this->assertInstanceOf(EqualsMatcher::class, $matcher);

        $value = array('foo');
        $this->assertNull($matcher->doArgumentsMatch($value));
    }

    /**
     * Tests creating a pass through matcher
     */
    public function testPassThroughMatcher()
    {
        $matcher = $this->getMockBuilder(IChainableArgumentMatcher::class)->getMock();

        $retMatcher = $this->factory->createMatcher($matcher);

        $this->assertSame($matcher, $retMatcher);
    }

    /**
     * Tests creating a phpunit adapter matcher
     */
    public function testPHPUnit6Constraint()
    {
        $matcher = $this->getMockBuilder(Constraint::class)->getMock();
        $matcher->expects($this->once())
            ->method('evaluate')
            ->with($this->equalTo('foo'))
            ->will($this->returnValue(true));

        $retMatcher = $this->factory->createMatcher($matcher);

        $value = array('foo');
        $this->assertNull($retMatcher->doArgumentsMatch($value));
    }

    /**
     * Tests creating a hamcrest adapter matcher
     */
    public function testHamcrestMatcher()
    {
        $matcher = $this->getMockBuilder('Hamcrest\Matcher')->getMock();
        $matcher->expects($this->once())
            ->method('matches')
            ->with($this->equalTo('foo'))
            ->will($this->returnValue(true));

        $retMatcher = $this->factory->createMatcher($matcher);

        $value = array('foo');
        $this->assertNull($retMatcher->doArgumentsMatch($value));
    }

    public function testOldMatcherAdaptsToNewFormat()
    {
        $oldMatcher = Phake::mock(IArgumentMatcher::class);
        $newMatcher = $this->factory->createMatcher($oldMatcher);

        $this->assertNotSame($oldMatcher, $newMatcher);

        $this->assertInstanceOf(IChainableArgumentMatcher::class, $newMatcher);
    }

    public function testMatcherSetsNextMatcherInChain()
    {
        $matcher1 = Phake::mock(IChainableArgumentMatcher::class);
        $matcher2 = Phake::mock(IChainableArgumentMatcher::class);
        /* @var $newMatcher Phake_Matchers_IChainableArgumentMatcher */
        $this->factory->createMatcher($matcher2);
        $this->factory->createMatcher($matcher1, $matcher2);

        $this->assertNull($matcher2->getNextMatcher());
        Phake::verify($matcher1)->setNextMatcher(Phake::equalTo($matcher2));
    }

    public function testMatcherChainReturnsAMatcherChain()
    {
        $matcher1 = Phake::mock(IChainableArgumentMatcher::class);
        $matcher2 = Phake::mock(IChainableArgumentMatcher::class);
        $matcher3 = Phake::mock(IChainableArgumentMatcher::class);

        $matcherChain = $this->factory->createMatcherChain(array($matcher1, $matcher2, $matcher3));

        $this->assertSame($matcher1, $matcherChain);

        Phake::verify($matcher1)->setNextMatcher($this->equalTo($matcher2));
        Phake::verify($matcher2)->setNextMatcher($this->equalTo($matcher3));
        Phake::verify($matcher3, Phake::never())->setNextMatcher(Phake::anyParameters());
    }

    public function testMatcherChainReturnsNullOnNoArguments()
    {
        $this->assertNull($this->factory->createMatcherChain(array()));
    }
}
