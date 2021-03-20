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

class CallVerifierProxyTest extends TestCase
{
    /**
     * @var Phake\Proxies\CallVerifierProxy
     */
    private $proxy;

    /**
     * @var Phake\CallRecorder\Recorder
     */
    private $obj;

    /**
     * @var Phake\Client\IClient
     */
    private $client;

    /**
     * Sets up test fixture
     */
    public function setUp(): void
    {
        $this->client     = new Phake\Client\DefaultClient();
        $this->obj        = new Phake\CallRecorder\Recorder();

        $matcher1 = new Phake\Matchers\EqualsMatcher('foo', \SebastianBergmann\Comparator\Factory::getInstance());
        $matcher2 = new Phake\Matchers\EqualsMatcher(array(), \SebastianBergmann\Comparator\Factory::getInstance());
        $matcher1->setNextMatcher($matcher2);
        $this->proxy = new CallVerifierProxy($matcher1, $this->client, false);

    }

    /**
     * Tests setting a stub on a method in the stubbable object
     */
    public function testIsCalledOn()
    {
        $mock = Phake::mock('PhakeTest_MagicClass');
        $mock->foo();

        $verifier = $this->proxy->isCalledOn($mock);

        $this->assertEquals(1, count($verifier));
    }

    public function testStaticIsCalledOn()
    {
        $matcher1 = new Phake\Matchers\EqualsMatcher('foo', \SebastianBergmann\Comparator\Factory::getInstance());
        $matcher2 = new Phake\Matchers\EqualsMatcher(array(), \SebastianBergmann\Comparator\Factory::getInstance());
        $matcher1->setNextMatcher($matcher2);
        $this->proxy = new CallVerifierProxy($matcher1, $this->client, true);

        $mock = Phake::mock('PhakeTest_MagicClass');
        $mock::foo();

        $verifier = $this->proxy->isCalledOn($mock);

        $this->assertEquals(1, count($verifier));
    }
}


