<?php declare(strict_types=1);

namespace Phake\CallRecorder;
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

class CallExpectationTest extends TestCase
{
    public function testToString()
    {
        /** @var $mock \Phake\IMock */
        $mock = Phake::mock('Phake\IMock');

        $matcher1 = Phake::mock('Phake\Matchers\IChainableArgumentMatcher');
        Phake::when($matcher1)->__toString()->thenReturn('100');
        $matcher2 = Phake::mock('Phake\Matchers\IChainableArgumentMatcher');
        Phake::when($matcher2)->__toString()->thenReturn('200');

        Phake::when($matcher1)->getNextMatcher->thenReturn($matcher2);

        $verifierMode = Phake::mock('Phake\CallRecorder\IVerifierMode');
        Phake::when($verifierMode)->__toString()->thenReturn('2 times');

        $expectation = new Phake\CallRecorder\CallExpectation($mock, 'method', $matcher1, $verifierMode);
        $this->assertEquals(
            "Expected Phake\IMock->method(100, 200) to be called 2 times",
            $expectation->__toString()
        );
    }

    public function testStaticToString()
    {
        /** @var $mock \Phake\IMock */
        $mock = Phake::mock('Phake\IMock');

        $matcher1 = Phake::mock('Phake\Matchers\IChainableArgumentMatcher');
        Phake::when($matcher1)->__toString()->thenReturn('100');
        $matcher2 = Phake::mock('Phake\Matchers\IChainableArgumentMatcher');
        Phake::when($matcher2)->__toString()->thenReturn('200');

        Phake::when($matcher1)->getNextMatcher->thenReturn($matcher2);

        $verifierMode = Phake::mock('Phake\CallRecorder\IVerifierMode');
        Phake::when($verifierMode)->__toString()->thenReturn('2 times');

        $expectation = new Phake\CallRecorder\CallExpectation(get_class($mock), 'method', $matcher1, $verifierMode);
        $this->assertEquals(
            "Expected Phake\IMock::method(100, 200) to be called 2 times",
            $expectation->__toString()
        );
    }
}
