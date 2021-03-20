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

/**
 * Description of VerifierTest
 *
 * @author Mike Lively <m@digitalsandwich.com>
 */
class VerifierTest extends TestCase
{
    /**
     * @var Recorder
     */
    private $recorder;

    /**
     * @var Verifier
     */
    private $verifier;

    /**
     * @var array
     */
    private $callArray;

    /**
     * @var IVerifierMode
     */
    private $verifierMode;

    /**
     * @var \Phake\IMock
     */
    private $obj;

    /**
     * Sets up the verifier and its call recorder
     */
    public function setUp(): void
    {
        $this->obj        = Phake::mock(\Phake\IMock::class);

        $this->recorder     = Phake::mock(Recorder::class);
        $this->verifierMode = Phake::mock(IVerifierMode::class);

        $this->callArray = array(
            new Call($this->obj, 'foo', array()),
            new Call($this->obj, 'bar', array()),
            new Call($this->obj, 'foo', array(
                'bar',
                'foo'
            )),
            new Call($this->obj, 'foo', array()),
        );

        Phake::when($this->recorder)->getAllCalls()->thenReturn($this->callArray);

        $this->verifier = new Verifier($this->recorder, $this->obj);
    }

    /**
     * Tests that a verifier can find a call that has been recorded.
     */
    public function testVerifierFindsCall()
    {
        $expectation = new CallExpectation(
            $this->obj,
            'bar',
            null,
            $this->verifierMode
        );
        $return      = new CallInfo($this->callArray[1], new Position(0));
        Phake::when($this->recorder)->getCallInfo($this->callArray[1])->thenReturn($return);

        Phake::when($this->verifierMode)->verify(Phake::anyParameters())->thenReturn(
            new VerifierMode\Result(true, '')
        );
        $this->assertEquals(
            new VerifierResult(true, array($return)),
            $this->verifier->verifyCall($expectation)
        );
    }

    /**
     * Tests that a verifier will not find a call that has not been recorded.
     */
    public function testVerifierDoesNotFindCall()
    {
        $expectation = new CallExpectation(
            $this->obj,
            'test',
            null,
            $this->verifierMode
        );
        Phake::when($this->verifierMode)->verify(Phake::anyParameters())->thenReturn(
            new VerifierMode\Result(true, '')
        );

        $result = $this->verifier->verifyCall($expectation)->getMatchedCalls();
        $this->assertTrue(is_array($result), 'verifyCall did not return an array');
        $this->assertTrue(empty($result), 'test call was found but should not have been');
    }

    /**
     * Tests that a verifier will not find a call that has been recorded with non matching parameters.
     */
    public function testVerifierDoesNotFindCallWithUnmatchedArguments()
    {
        $matcher1 = new \Phake\Matchers\EqualsMatcher('test', \SebastianBergmann\Comparator\Factory::getInstance());
        $matcher2 = new \Phake\Matchers\EqualsMatcher('test', \SebastianBergmann\Comparator\Factory::getInstance());
        $matcher1->setNextMatcher($matcher2);
        $expectation = new CallExpectation(
            $this->obj,
            'foo',
            $matcher1,
            $this->verifierMode
        );
        Phake::when($this->verifierMode)->verify(Phake::anyParameters())->thenReturn(
            new VerifierMode\Result(true, '')
        );

        $result = $this->verifier->verifyCall($expectation)->getMatchedCalls();
        $this->assertTrue(empty($result));
    }

    /**
     * Tests that a verifier returns an array of call info objects when it finds a call that matches
     */
    public function testVerifierReturnsCallInfoForMatchedCalls()
    {
        $expectation = new CallExpectation(
            $this->obj,
            'foo',
            null,
            $this->verifierMode
        );

        $return = new CallInfo($this->callArray[1], new Position(0));
        Phake::when($this->recorder)->getCallInfo(Phake::anyParameters())->thenReturn($return);

        Phake::when($this->verifierMode)->verify(Phake::anyParameters())->thenReturn(
            new VerifierMode\Result(true, '')
        );

        $this->verifier->verifyCall($expectation);

        $this->assertEquals(
            new VerifierResult(true, array($return, $return)),
            $this->verifier->verifyCall($expectation)
        );
    }


    /**
     * Tests that a verifier can find a call using AnyParameters matcher
     */
    public function testVerifierFindsCallWithAnyParameters()
    {
        $expectation = new CallExpectation(
            $this->obj,
            'bar',
            new \Phake\Matchers\AnyParameters(),
            $this->verifierMode
        );

        $return = new CallInfo($this->callArray[1], new Position(0));
        Phake::when($this->recorder)->getCallInfo($this->callArray[1])->thenReturn($return);

        Phake::when($this->verifierMode)->verify(Phake::anyParameters())->thenReturn(
            new VerifierMode\Result(true, '')
        );

        $this->assertEquals(
            new VerifierResult(true, array($return)),
            $this->verifier->verifyCall($expectation),
            'bar call was not found'
        );
    }

    /**
     * Tests that the verifier will only return calls made on the same object
     */
    public function testVerifierBeingCalledWithMixedCallRecorder()
    {
        $recorder = new Recorder();

        $obj1     = $this->getMockBuilder('Phake\IMock')
                        ->getMock();
        $obj2     = $this->getMockBuilder('Phake\IMock')
                        ->getMock();

        $expectation = new CallExpectation(
            $obj1,
            'foo',
            null,
            $this->verifierMode
        );

        $recorder->recordCall(new Call($obj1, 'foo', array()));
        $recorder->recordCall(new Call($obj2, 'foo', array()));

        $verifier = new Verifier($recorder, $obj1);

        Phake::when($this->verifierMode)->verify(Phake::anyParameters())->thenReturn(
            new VerifierMode\Result(true, '')
        );

        $this->assertEquals(1, count($verifier->verifyCall($expectation)->getMatchedCalls()));
    }

    public function testVerifierChecksVerificationMode()
    {
        $expectation = new CallExpectation(
            $this->obj,
            'foo',
            null,
            $this->verifierMode
        );

        $return = new CallInfo($this->callArray[1], new Position(0));
        Phake::when($this->recorder)->getCallInfo(Phake::anyParameters())->thenReturn($return);

        Phake::when($this->verifierMode)->verify(Phake::anyParameters())->thenReturn(
            new VerifierMode\Result(true, '')
        );

        $this->verifier->verifyCall($expectation);

        Phake::verify($this->verifierMode)->verify(Phake::capture($verifyCallInfo));
        $this->assertEquals(array($return, $return), $verifyCallInfo);
    }

    public function testVerifierReturnsFalseWhenAnExpectationIsNotMet()
    {
        $expectation = new CallExpectation(
            $this->obj,
            'foo',
            null,
            $this->verifierMode
        );

        Phake::when($this->verifierMode)->__toString()->thenReturn('exactly 1 times');

        $return = new CallInfo($this->callArray[1], new Position(0));
        Phake::when($this->recorder)->getCallInfo(Phake::anyParameters())->thenReturn($return);

        Phake::when($this->verifierMode)->verify(Phake::anyParameters())->thenReturn(
            new VerifierMode\Result(false, 'actually called 0 times')
        );

        $expectedMessage = 'Expected Phake\IMock->foo() to be called exactly 1 times, actually called 0 times.
Other Invocations:
===
  Phake\IMock->foo(<string:bar>, <string:foo>)
  No matchers were given to Phake::when(), but arguments were received by this method.
===';

        $this->assertEquals(
            new VerifierResult(false, array(), $expectedMessage),
            $this->verifier->verifyCall($expectation)
        );
    }

    public function testVerifierModifiesFailureDescriptionIfThereAreNoInteractions()
    {
        $obj2        = Phake::mock('Phake\IMock');

        $expectation = new CallExpectation(
            $obj2,
            'foo',
            null,
            $this->verifierMode
        );

        Phake::when($this->verifierMode)->__toString()->thenReturn('exactly 1 times');

        $return = new CallInfo($this->callArray[1], new Position(0));
        Phake::when($this->recorder)->getCallInfo(Phake::anyParameters())->thenReturn($return);

        Phake::when($this->verifierMode)->verify(Phake::anyParameters())->thenReturn(
            new VerifierMode\Result(false, 'actually called 0 times')
        );

        $this->assertEquals(
            new VerifierResult(false, array(), 'Expected Phake\IMock->foo() to be called exactly 1 times, actually called 0 times. In fact, there are no interactions with this mock.'),
            $this->verifier->verifyCall($expectation)
        );

        Phake::verify($this->verifierMode)->verify(array());
    }

    public function testVerifierModifiesFailureDescriptionWithOtherCalls()
    {
        $expectation = new CallExpectation(
            $this->obj,
            'foo',
            new \Phake\Matchers\EqualsMatcher('test', \SebastianBergmann\Comparator\Factory::getInstance()),
            $this->verifierMode
        );

        Phake::when($this->verifierMode)->__toString()->thenReturn('exactly 1 times');

        $return = new CallInfo($this->callArray[1], new Position(0));
        Phake::when($this->recorder)->getCallInfo(Phake::anyParameters())->thenReturn($return);

        Phake::when($this->verifierMode)->verify(Phake::anyParameters())->thenReturn(
            new VerifierMode\Result(false, 'actually called 0 times')
        );

        $expected_msg =
            "Expected Phake\IMock->foo(equal to <string:test>) to be called exactly 1 times, actually called 0 times.\n"
                . "Other Invocations:\n"
                . "===\n"
                . "  Phake\IMock->foo()\n"
                . "  Argument #1 failed test\n"
                . "  Failed asserting that null matches expected 'test'.\n"
                . "===\n"
                . "  Phake\IMock->foo(<string:bar>, <string:foo>)\n"
                . "  Argument #1 failed test\n"
                . "  Failed asserting that two strings are equal.\n"
                . "  \n"
                . "  --- Expected\n"
                . "  +++ Actual\n"
                . "  @@ @@\n"
                . "  -'test'\n"
                . "  +'bar'\n"
                . "===\n"
                . "  Phake\IMock->foo()\n"
                . "  Argument #1 failed test\n"
                . "  Failed asserting that null matches expected 'test'.\n"
                . "===";

        $this->assertEquals(
            new VerifierResult(false, array(), $expected_msg),
            $this->verifier->verifyCall($expectation)
        );
    }

    public function testVerifyNoCalls()
    {
        Phake::when($this->recorder)->getAllCalls()->thenReturn(array());

        $this->assertEquals(new VerifierResult(true, array()), $this->verifier->verifyNoCalls());
    }

    public function testVerifyNoCallsFailsWithOtherCallsListed()
    {
        $expected_msg =
            "Expected no interaction with mock\n"
                . "Invocations:\n"
                . "  Phake\IMock->foo()\n"
                . "  Phake\IMock->bar()\n"
                . "  Phake\IMock->foo(<string:bar>, <string:foo>)\n"
                . "  Phake\IMock->foo()";

        $this->assertEquals(
            new VerifierResult(false, array(), $expected_msg),
            $this->verifier->verifyNoCalls()
        );
    }

    public function testVerifyMarksMatchedCallsAsVerified()
    {
        $expectation = new CallExpectation(
            $this->obj,
            'bar',
            null,
            $this->verifierMode
        );
        $return = new CallInfo($this->callArray[1], new Position(0));
        Phake::when($this->recorder)->getCallInfo($this->callArray[1])->thenReturn($return);

        Phake::when($this->verifierMode)->verify(Phake::anyParameters())->thenReturn(
            new VerifierMode\Result(true, '')
        );

        $this->verifier->verifyCall($expectation);
        Phake::verify($this->recorder)->markCallVerified($this->callArray[1]);
        Phake::verify($this->recorder)->markCallVerified(Phake::anyParameters());
    }

    public function testVerifyNoOtherCallsSucceeds()
    {
        Phake::when($this->recorder)->getUnverifiedCalls()->thenReturn($this->callArray);
        $verifierResult = $this->verifier->verifyNoOtherCalls();

        $this->assertFalse($verifierResult->getVerified());
        $expected_msg =
            "Expected no interaction with mock\n"
            . "Invocations:\n"
            . "  Phake\IMock->foo()\n"
            . "  Phake\IMock->bar()\n"
            . "  Phake\IMock->foo(<string:bar>, <string:foo>)\n"
            . "  Phake\IMock->foo()";

        $this->assertEquals($expected_msg, $verifierResult->getFailureDescription());
        $this->assertEmpty($verifierResult->getMatchedCalls());
    }

    public function testVerifyNoOtherCallsFails()
    {
        Phake::when($this->recorder)->getUnverifiedCalls()->thenReturn($this->callArray);
        $verifierResult = $this->verifier->verifyNoOtherCalls();

        $this->assertFalse($verifierResult->getVerified());
        $expected_msg =
            "Expected no interaction with mock\n"
            . "Invocations:\n"
            . "  Phake\IMock->foo()\n"
            . "  Phake\IMock->bar()\n"
            . "  Phake\IMock->foo(<string:bar>, <string:foo>)\n"
            . "  Phake\IMock->foo()";

        $this->assertEquals($expected_msg, $verifierResult->getFailureDescription());
        $this->assertEmpty($verifierResult->getMatchedCalls());
    }
}


