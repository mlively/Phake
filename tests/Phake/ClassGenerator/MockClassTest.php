<?php declare(strict_types=1);

namespace Phake\ClassGenerator;

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
 * Description of MockClass
 */
class MockClassTest extends TestCase
{
    /**
     * @var MockClass
     */
    private $classGen;

    /**
     * @Mock
     * @var Phake\Mock\InfoRegistry
     */
    private $infoRegistry;

    public function setUp(): void
    {
        Phake::initAnnotations($this);
        $this->classGen = new MockClass();
    }

    /**
     * Tests the generate method of the mock class generator.
     */
    public function testGenerateCreatesClass()
    {
        $newClassName = str_replace('\\', '_', __CLASS__) . '_TestClass1';
        $mockedClass  = 'stdClass';

        $this->assertFalse(
            class_exists($newClassName, false),
            'The class being tested for already exists. May have created a test reusing this class name.'
        );

        $this->classGen->generate($newClassName, $mockedClass, $this->infoRegistry);

        $this->assertTrue(
            class_exists($newClassName, false),
            'Phake\ClassGenerator\MockClass::generate() did not create correct class'
        );
    }

    /**
     * Tests that the generate method will create a class that extends a given class.
     */
    public function testGenerateCreatesClassExtendingExistingClass()
    {
        $newClassName = str_replace('\\', '_', __CLASS__) . '_TestClass2';
        $mockedClass  = 'stdClass';

        $this->classGen->generate($newClassName, $mockedClass, $this->infoRegistry);

        $rflClass = new \ReflectionClass($newClassName);

        $this->assertTrue(
            $rflClass->isSubclassOf($mockedClass),
            'Phake\ClassGenerator\MockClass::generate() did not create a class that extends mocked class.'
        );
    }

    /**
     * Tests that generated mock classes will accept and provide access too a call recorder.
     */
    public function testGenerateCreatesClassWithExposedCallRecorder()
    {
        $newClassName = str_replace('\\', '_', __CLASS__) . '_TestClass3';
        $mockedClass  = 'stdClass';

        $this->classGen->generate($newClassName, $mockedClass, $this->infoRegistry);

        $callRecorder = $this->getMockBuilder(Phake\CallRecorder\Recorder::class)->getMock();
        $stubMapper   = $this->getMockBuilder(Phake\Stubber\StubMapper::class)->getMock();
        $answer       = $this->getMockBuilder(Phake\Stubber\IAnswer::class)->getMock();
        $mock         = $this->classGen->instantiate($newClassName, $callRecorder, $stubMapper, $answer);

        $this->assertSame($callRecorder, Phake::getInfo($mock)->getCallRecorder());
    }

    /**
     * Tests that generated mock classes will record calls to mocked methods.
     */
    public function testCallingMockedMethodRecordsCall()
    {
        $newClassName = str_replace('\\', '_', __CLASS__) . '_TestClass4';
        $mockedClass  = 'PhakeTest_MockedClass';

        $this->classGen->generate($newClassName, $mockedClass, $this->infoRegistry);

        $callRecorder = $this->getMockBuilder(Phake\CallRecorder\Recorder::class)->getMock();
        $stubMapper   = $this->getMockBuilder(Phake\Stubber\StubMapper::class)->getMock();
        $answer       = new Phake\Stubber\Answers\NoAnswer();
        $mock         = $this->classGen->instantiate($newClassName, $callRecorder, $stubMapper, $answer);

        /* @var $callRecorder Phake\CallRecorder\Recorder */
        $callRecorder->expects($this->once())
            ->method('recordCall')
            ->with($this->equalTo(new Phake\CallRecorder\Call($mock, 'foo', array())));

        $mock->foo();
    }

    /**
     * Tests that calls are recorded with arguments
     */
    public function testCallingmockedMethodRecordsArguments()
    {
        $newClassName = str_replace('\\', '_', __CLASS__) . '_TestClass9';
        $mockedClass  = 'PhakeTest_MockedClass';

        $this->classGen->generate($newClassName, $mockedClass, $this->infoRegistry);

        $callRecorder = $this->getMockBuilder(Phake\CallRecorder\Recorder::class)->getMock();
        $stubMapper   = $this->getMockBuilder(Phake\Stubber\StubMapper::class)->getMock();
        $answer       = new Phake\Stubber\Answers\NoAnswer();
        $mock         = $this->classGen->instantiate($newClassName, $callRecorder, $stubMapper, $answer);

        /* @var $callRecorder Phake\CallRecorder\Recorder */
        $callRecorder->expects($this->once())
            ->method('recordCall')
            ->with(
                $this->equalTo(
                    new Phake\CallRecorder\Call($mock, 'fooWithArgument', array('bar'))
                )
            );

        $mock->fooWithArgument('bar');
    }

    public function testGeneratingClassFromMultipleInterfaces()
    {
        $newClassName = str_replace('\\', '_', __CLASS__) . '_testClass28';
        $mockedClass = array('PhakeTest_MockedInterface', 'PhakeTest_ConstructorInterface');

        $this->classGen->generate($newClassName, $mockedClass, $this->infoRegistry);

        $reflClass = new \ReflectionClass($newClassName);
        $this->assertTrue($reflClass->implementsInterface('PhakeTest_MockedInterface'), "Implements PhakeTest_MockedInterface");
        $this->assertTrue($reflClass->implementsInterface('PhakeTest_ConstructorInterface'), "Implements PhakeTest_ConstructorInterface");
    }

    public function testGeneratingClassFromSimilarInterfaces()
    {
        $newClassName = str_replace('\\', '_', __CLASS__) . '_testClass29';
        $mockedClass = array('PhakeTest_MockedInterface', 'PhakeTest_MockedInterface2');

        $this->classGen->generate($newClassName, $mockedClass, $this->infoRegistry);

        $reflClass = new \ReflectionClass($newClassName);
        $this->assertTrue($reflClass->implementsInterface('PhakeTest_MockedInterface'), "Implements PhakeTest_MockedInterface");
        $this->assertTrue($reflClass->implementsInterface('PhakeTest_MockedInterface2'), "Implements PhakeTest_ConstructorInterface");
    }

    public function testGeneratingClassFromDuplicateInterfaces()
    {
        $newClassName = str_replace('\\', '_', __CLASS__) . '_testClass30';
        $mockedClass = array('PhakeTest_MockedInterface', 'PhakeTest_MockedInterface');

        $this->classGen->generate($newClassName, $mockedClass, $this->infoRegistry);

        $reflClass = new \ReflectionClass($newClassName);
        $this->assertTrue($reflClass->implementsInterface('PhakeTest_MockedInterface'), "Implements PhakeTest_MockedInterface");
    }

    public function testGeneratingClassFromInheritedInterfaces()
    {
        $newClassName = str_replace('\\', '_', __CLASS__) . '_testClass31';
        $mockedClass = array('PhakeTest_MockedInterface', 'PhakeTest_MockedChildInterface');

        $this->classGen->generate($newClassName, $mockedClass, $this->infoRegistry);

        $reflClass = new \ReflectionClass($newClassName);
        $this->assertTrue($reflClass->implementsInterface('PhakeTest_MockedInterface'), "Implements PhakeTest_MockedInterface");
        $this->assertTrue($reflClass->implementsInterface('PhakeTest_MockedChildInterface'), "Implements PhakeTest_MockedInterface");
    }

    public function testGeneratingClassFromMultipleClasses()
    {
        $newClassName = str_replace('\\', '_', __CLASS__) . '_testClass32';
        $mockedClass = array('PhakeTest_MockedClass', 'PhakeTest_MockedConstructedClass');

        $this->expectException('RuntimeException');
        $this->classGen->generate($newClassName, $mockedClass, $this->infoRegistry);
    }

    /**
     * Tests the instantiation functionality of the mock generator.
     */
    public function testInstantiate()
    {
        $newClassName = str_replace('\\', '_', __CLASS__) . '_TestClass5';
        $mockedClass  = 'PhakeTest_MockedClass';

        $this->classGen->generate($newClassName, $mockedClass, $this->infoRegistry);

        $callRecorder = $this->getMockBuilder(Phake\CallRecorder\Recorder::class)->getMock();
        $stubMapper   = $this->getMockBuilder(Phake\Stubber\StubMapper::class)->getMock();
        $answer       = Phake::mock(Phake\Stubber\Answers\NoAnswer::class, Phake::ifUnstubbed()->thenCallParent());
        $mock         = $this->classGen->instantiate($newClassName, $callRecorder, $stubMapper, $answer);

        $this->assertInstanceOf($newClassName, $mock);
    }

    /**
     * Tests that calling a stubbed method will result in the stubbed answer being returned.
     * @group testonly
     */
    public function testStubbedMethodsReturnStubbedAnswer()
    {
        $newClassName = str_replace('\\', '_', __CLASS__) . '_TestClass7';
        $mockedClass  = 'PhakeTest_MockedClass';

        $this->classGen->generate($newClassName, $mockedClass, $this->infoRegistry);

        $callRecorder = $this->getMockBuilder(Phake\CallRecorder\Recorder::class)->getMock();
        $stubMapper   = $this->getMockBuilder(Phake\Stubber\StubMapper::class)->getMock();
        $answer       = Phake::mock(Phake\Stubber\Answers\NoAnswer::class, Phake::ifUnstubbed()->thenCallParent());
        $mock         = $this->classGen->instantiate($newClassName, $callRecorder, $stubMapper, $answer);

        $answer       = Phake::mock(Phake\Stubber\Answers\NoAnswer::class, Phake::ifUnstubbed()->thenCallParent());

        $stubMapper->expects($this->once())
            ->method('getStubByCall')
            ->with($this->equalTo('fooWithArgument'), array('bar'))
            ->will($this->returnValue(new Phake\Stubber\AnswerCollection($answer)));

        $mock->fooWithArgument('bar');

        Phake::verify($answer)->getAnswerCallback($mock, 'fooWithArgument');
    }

    /**
     * Tests that default parameters work correctly with stubbing
     */
    public function testStubbedMethodDoesNotCheckUnpassedDefaultParameters()
    {
        $newClassName = str_replace('\\', '_', __CLASS__) . '_TestClass23';
        $mockedClass  = 'PhakeTest_MockedClass';

        $this->classGen->generate($newClassName, $mockedClass, $this->infoRegistry);

        $callRecorder = $this->getMockBuilder(Phake\CallRecorder\Recorder::class)->getMock();
        $stubMapper   = $this->getMockBuilder(Phake\Stubber\StubMapper::class)->getMock();
        $answer       = Phake::mock(Phake\Stubber\Answers\NoAnswer::class, Phake::ifUnstubbed()->thenCallParent());
        $mock         = $this->classGen->instantiate($newClassName, $callRecorder, $stubMapper, $answer);

        $answer       = Phake::mock(Phake\Stubber\Answers\NoAnswer::class, Phake::ifUnstubbed()->thenCallParent());
        $stubMapper->expects($this->once())
            ->method('getStubByCall')
            ->with($this->equalTo('fooWithDefault'), array())
            ->will($this->returnValue(new Phake\Stubber\AnswerCollection($answer)));

        $mock->fooWithDefault();

        Phake::verify($answer)->getAnswerCallback($mock, 'fooWithDefault');
    }

    /**
     * Tests that generated mock classes will allow setting stubs to methods. This is delegated
     * internally to the stubMapper
     */
    public function testStubbableInterface()
    {
        $newClassName = str_replace('\\', '_', __CLASS__) . '_TestClass8';
        $mockedClass  = 'stdClass';

        $this->classGen->generate($newClassName, $mockedClass, $this->infoRegistry);

        /** @var $callRecorder Phake\CallRecorder\Recorder */
        $callRecorder = $this->getMockBuilder(Phake\CallRecorder\Recorder::class)->getMock();
        /** @var $stubMapper Phake\Stubber\StubMapper */
        $stubMapper = $this->getMockBuilder(Phake\Stubber\StubMapper::class)->getMock();
        $answer     = $this->getMockBuilder(Phake\Stubber\IAnswer::class)->getMock();
        $mock       = $this->classGen->instantiate($newClassName, $callRecorder, $stubMapper, $answer);

        $answer           = $this->getMockBuilder(Phake\Stubber\IAnswer::class)->getMock();
        $answerCollection = new Phake\Stubber\AnswerCollection($answer);
        $matcher          = $this->getMockBuilder(Phake\Matchers\MethodMatcher::class)
                                ->disableOriginalConstructor()
                                ->getMock();

        $stubMapper->expects($this->once())
            ->method('mapStubToMatcher')
            ->with($this->equalTo($answerCollection), $this->equalTo($matcher));

        Phake::getInfo($mock)->getStubMapper()->mapStubToMatcher($answerCollection, $matcher);
    }

    /**
     * Tests that calling an unstubbed method will result in the default answer being returned.
     */
    public function testUnstubbedMethodsReturnDefaultAnswer()
    {
        $newClassName = str_replace('\\', '_', __CLASS__) . '_TestClass11';
        $mockedClass  = 'PhakeTest_MockedClass';

        $this->classGen->generate($newClassName, $mockedClass, $this->infoRegistry);

        $callRecorder = $this->getMockBuilder(Phake\CallRecorder\Recorder::class)->getMock();
        $stubMapper   = $this->getMockBuilder(Phake\Stubber\StubMapper::class)->getMock();
        $answer       = Phake::mock(Phake\Stubber\Answers\NoAnswer::class, Phake::ifUnstubbed()->thenCallParent());

        $mock = $this->classGen->instantiate($newClassName, $callRecorder, $stubMapper, $answer);

        $mock->fooWithArgument('bar');

        Phake::verify($answer)->getAnswerCallback($mock, 'fooWithArgument');
    }

    /**
     * Tests that __call on an unmatched method will return a default value
     */
    public function testUnstubbedCallReturnsDefaultAnswer()
    {
        $newClassName = str_replace('\\', '_', __CLASS__) . '_TestClass19';
        $mockedClass  = 'PhakeTest_MagicClass';

        $this->classGen->generate($newClassName, $mockedClass, $this->infoRegistry);

        $callRecorder = $this->getMockBuilder(Phake\CallRecorder\Recorder::class)->getMock();
        $stubMapper   = $this->getMockBuilder(Phake\Stubber\StubMapper::class)->getMock();
        $answer       = Phake::mock(Phake\Stubber\Answers\NoAnswer::class, Phake::ifUnstubbed()->thenCallParent());

        $mock = $this->classGen->instantiate($newClassName, $callRecorder, $stubMapper, $answer);

        $mock->fooWithArgument('bar');

        Phake::verify($answer)->getAnswerCallback($mock, '__call');
    }

    public function testMagicCallMethodsRecordTwice()
    {
        $newClassName = str_replace('\\', '_', __CLASS__) . '_TestClass21';
        $mockedClass  = 'PhakeTest_MagicClass';

        $this->classGen->generate($newClassName, $mockedClass, $this->infoRegistry);

        $callRecorder = Phake::mock(Phake\CallRecorder\Recorder::class);
        $stubMapper   = $this->getMockBuilder(Phake\Stubber\StubMapper::class)->getMock();
        $answer       = Phake::mock(Phake\Stubber\Answers\NoAnswer::class, Phake::ifUnstubbed()->thenCallParent());
        $mock         = $this->classGen->instantiate($newClassName, $callRecorder, $stubMapper, $answer);

        $mock->foo('blah');

        Phake::verify($callRecorder)->recordCall(
            new Phake\CallRecorder\Call($mock, 'foo', array('blah'))
        );
        Phake::verify($callRecorder)->recordCall(
            new Phake\CallRecorder\Call($mock, '__call', array('foo', array('blah')))
        );
    }

    public function testMagicCallChecksFallbackStub()
    {
        $newClassName = str_replace('\\', '_', __CLASS__) . '_TestClass22';
        $mockedClass  = 'PhakeTest_MagicClass';

        $this->classGen->generate($newClassName, $mockedClass, $this->infoRegistry);

        $callRecorder = Phake::mock(Phake\CallRecorder\Recorder::class);
        $stubMapper   = Phake::mock(Phake\Stubber\StubMapper::class);
        $answer       = Phake::mock(Phake\Stubber\Answers\NoAnswer::class, Phake::ifUnstubbed()->thenCallParent());
        $mock         = $this->classGen->instantiate($newClassName, $callRecorder, $stubMapper, $answer);


        $mock->foo('blah');

        Phake::verify($stubMapper)->getStubByCall('foo', array('blah'));
        Phake::verify($stubMapper)->getStubByCall('__call', array('foo', array('blah')));
    }

    /**
     * Tests generating a class definition for a mocked interface
     */
    public function testGenerateOnInterface()
    {
        $newClassName = str_replace('\\', '_', __CLASS__) . '_TestClass13';
        $mockedClass  = 'PhakeTest_MockedInterface';

        $this->classGen->generate($newClassName, $mockedClass, $this->infoRegistry);

        $this->assertTrue(
            class_exists($newClassName, false),
            'Phake\ClassGenerator\MockClass::generate() did not create correct class'
        );
    }

    /**
     * Test retrieving mock name
     */
    public function testMockName()
    {
        $newClassName = str_replace('\\', '_', __CLASS__) . '_TestClass18';
        $mockedClass  = 'PhakeTest_MockedClass';

        $this->classGen->generate($newClassName, $mockedClass, $this->infoRegistry);

        $callRecorder = $this->getMockBuilder(Phake\CallRecorder\Recorder::class)->getMock();
        $stubMapper   = $this->getMockBuilder(Phake\Stubber\StubMapper::class)->getMock();
        $answer       = $this->getMockBuilder(Phake\Stubber\IAnswer::class)->getMock();
        $mock         = $this->classGen->instantiate($newClassName, $callRecorder, $stubMapper, $answer);

        $this->assertEquals('PhakeTest_MockedClass', $mock::__PHAKE_name);
        $this->assertEquals('PhakeTest_MockedClass', Phake::getInfo($mock)->getName());
    }

    /**
     * Tests that passing constructor arguments to the derived class will cause the original constructor to be called.
     */
    public function testCallingOriginalConstructor()
    {
        $newClassName = str_replace('\\', '_', __CLASS__) . '_TestClass16';
        $mockedClass  = 'PhakeTest_MockedConstructedClass';
        $this->classGen->generate($newClassName, $mockedClass, $this->infoRegistry);

        /** @var $callRecorder Phake#CallRecorder\Recorder */
        $callRecorder = $this->getMockBuilder(Phake\CallRecorder\Recorder::class)->getMock();
        /** @var $stubMapper Phake\Stubber\StubMapper */
        $stubMapper = $this->getMockBuilder(Phake\Stubber\StubMapper::class)->getMock();
        $answer     = new Phake\Stubber\Answers\ParentDelegate();
        $mock       = $this->classGen->instantiate(
            $newClassName,
            $callRecorder,
            $stubMapper,
            $answer,
            array('val1', 'val2', 'val3')
        );

        $this->assertEquals('val1', $mock->getProp1());
        $this->assertEquals('val2', $mock->getProp2());
        $this->assertEquals('val3', $mock->getProp3());
    }

    /**
     * Tests that passing constructor arguments to the derived class will cause the original constructor to be called.
     */
    public function testCallingFinalOriginalConstructor()
    {
        $newClassName = str_replace('\\', '_', __CLASS__) . '_TestClass26';
        $mockedClass  = 'PhakeTest_MockedFinalConstructedClass';
        $this->classGen->generate($newClassName, $mockedClass, $this->infoRegistry);

        /** @var $callRecorder Phake#CallRecorder\Recorder */
        $callRecorder = $this->getMockBuilder(Phake\CallRecorder\Recorder::class)->getMock();
        /** @var $stubMapper Phake\Stubber\StubMapper */
        $stubMapper = $this->getMockBuilder(Phake\Stubber\StubMapper::class)->getMock();
        $answer     = new Phake\Stubber\Answers\ParentDelegate();
        $mock       = $this->classGen->instantiate(
            $newClassName,
            $callRecorder,
            $stubMapper,
            $answer,
            array('val1', 'val2', 'val3')
        );

        $this->assertEquals('val1', $mock->getProp1());
        $this->assertEquals('val2', $mock->getProp2());
        $this->assertEquals('val3', $mock->getProp3());
    }


    /**
     * Tests the generate method of the mock class generator.
     */
    public function testGenerateCreatesClassWithConstructorInInterfaceButNotInAbstractClass()
    {
        $newClassName = str_replace('\\', '_', __CLASS__) . '_TestClass27';
        $mockedClass = 'PhakeTest_ImplementConstructorInterface';

        $this->assertFalse(
            class_exists($newClassName, false),
            'The class being tested for already exists. May have created a test reusing this class name.'
        );

        $this->classGen->generate($newClassName, $mockedClass, $this->infoRegistry);

        $this->assertTrue(
            class_exists($newClassName, false),
            'Phake\ClassGenerator\MockClass::generate() did not create correct class'
        );
    }

    /**
     * Tests that final methods are not overridden
     */
    public function testFinalMethodsAreNotOverridden()
    {
        $newClassName = str_replace('\\', '_', __CLASS__) . '_TestClass17';
        $mockedClass  = 'PhakeTest_FinalMethod';

        $this->classGen->generate($newClassName, $mockedClass, $this->infoRegistry);

        $this->addToAssertionCount(1);
    }

    public function testMockFinalClassThrowsException()
    {
        $expectedException = new \InvalidArgumentException("Final classes cannot be mocked.");

        try {
            $mock = Phake::mock('PhakeTest_FinalClass');
            $this->fail("The mocked final method did not throw an exception");
        } catch (\InvalidArgumentException $actualException) {
            $this->assertEquals($actualException, $expectedException);
        }
    }

    /**
     * Tests that the mocked object's __toString() method returns a string by default.
     */
    public function testToStringReturnsString()
    {
        $newClassName = str_replace('\\', '_', __CLASS__) . '_TestClass24';
        $mockedClass  = 'PhakeTest_ToStringMethod';
        $this->classGen->generate($newClassName, $mockedClass, $this->infoRegistry);

        /** @var $recorder Phake#CallRecorder\Recorder */
        $recorder = $this->getMockBuilder(Phake\CallRecorder\Recorder::class)->getMock();
        $mapper   = new Phake\Stubber\StubMapper();
        $answer   = new Phake\Stubber\Answers\ParentDelegate();

        $mock = $this->classGen->instantiate($newClassName, $recorder, $mapper, $answer);

        $string = $mock->__toString();

        $this->assertNotNull($string, '__toString() should not return NULL');
        $this->assertEquals('Mock for PhakeTest_ToStringMethod', $string);
    }

    public function testDestructMocked()
    {
        $newClassName = str_replace('\\', '_', __CLASS__) . '_TestClass' . bin2hex(random_bytes(7));
        $mockedClass  = 'PhakeTest_DestructorClass';
        $this->classGen->generate($newClassName, $mockedClass, $this->infoRegistry);

        /** @var $recorder Phake#CallRecorder\Recorder */
        $recorder = $this->getMockBuilder(Phake\CallRecorder\Recorder::class)->getMock();
        $mapper   = new Phake\Stubber\StubMapper();
        $answer   = new Phake\Stubber\Answers\ParentDelegate();

        $mock = $this->classGen->instantiate($newClassName, $recorder, $mapper, $answer);

        \PhakeTest_DestructorClass::$destructCalled = false;
        unset($mock);
        $this->assertFalse(\PhakeTest_DestructorClass::$destructCalled, "It appears that the destructor was called");
    }

    public function testSerializableMock()
    {
        $newClassName = str_replace('\\', '_', __CLASS__) . '_TestClass' . uniqid();
        $mockedClass  = 'PhakeTest_SerializableClass';
        $this->classGen->generate($newClassName, $mockedClass, $this->infoRegistry);

        /** @var $recorder Phake#CallRecorder\Recorder */
        $recorder = $this->getMockBuilder(Phake\CallRecorder\Recorder::class)->getMock();
        $mapper   = new Phake\Stubber\StubMapper();
        $answer   = new Phake\Stubber\Answers\ParentDelegate();

        try {
            $mock = $this->classGen->instantiate($newClassName, $recorder, $mapper, $answer);
            $this->assertInstanceOf('PhakeTest_SerializableClass', $mock);
        } catch (\Exception $e) {
            $this->fail("Can't instantiate Serializable Object");
        }
    }

    public function testMocksTraversable()
    {
        $this->assertInstanceOf('Traversable', Phake::mock('Traversable'));
    }

    public function testTraversableExtendedInterfaceIncludesOriginalInterface()
    {
        $this->assertInstanceOf('PhakeTest_TraversableInterface', Phake::mock('PhakeTest_TraversableInterface'));
    }

    /**
     * Ensure that 'callable' type hints in method parameters are supported.
     */
    public function testCallableTypeHint()
    {
        $this->assertInstanceOf('PhakeTest_CallableTypehint', Phake::mock('PhakeTest_CallableTypehint'));
    }

    public function testMockVariableNumberOfArguments()
    {
        $mockedClass = Phake::mock('PhakeTest_MockedClass');
        list($arg1, $arg2, $arg3) = array(1, 2, 3);
        $mockedClass->fooWithVariableNumberOfArguments($arg1, $arg2, $arg3);

        Phake::verify($mockedClass)->fooWithVariableNumberOfArguments(1, 2, 3);
    }

    public function testGeneratedMockClassHasStaticInfo()
    {
        $newClassName = str_replace('\\', '_', __CLASS__) . '_TestClass' . bin2hex(random_bytes(7));
        $mockedClass  = 'stdClass';
        $this->classGen->generate($newClassName, $mockedClass, $this->infoRegistry);

        /* @var $info Phake\Mock\Info */
        $info = $newClassName::$__PHAKE_staticInfo;
        $this->assertInstanceOf(Phake\Mock\Info::class, $info);

        $this->assertInstanceOf(Phake\Stubber\IAnswer::class, $info->getDefaultAnswer());
        $this->assertEquals($mockedClass, $info->getName());
        $this->assertInstanceOf(Phake\CallRecorder\Recorder::class, $info->getCallRecorder());
        $this->assertInstanceOf(Phake\Stubber\StubMapper::class, $info->getStubMapper());
        $this->assertInstanceOf(InvocationHandler\IInvocationHandler::class, $info->getHandlerChain());
    }

    public function testGeneratedMockAddsSelfToRegistry()
    {
        $newClassName = str_replace('\\', '_', __CLASS__) . '_TestClass' . uniqid();
        $mockedClass  = 'stdClass';
        $this->classGen->generate($newClassName, $mockedClass, $this->infoRegistry);

        Phake::verify($this->infoRegistry)->addInfo($newClassName::$__PHAKE_staticInfo);
    }

    /**
     * Test that the generated mock has the same doc mocked class
     */
    public function testGenerateMaintainsPhpDoc()
    {
        $newClassName = str_replace('\\', '_', __CLASS__) . '_TestClass25';
        $mockedClass = 'PhakeTest_MockedClass';

        $this->classGen->generate($newClassName, $mockedClass, $this->infoRegistry);

        $rflClass = new \ReflectionClass($newClassName);

        $this->assertFalse($rflClass->getMethod("foo")->getDocComment());
        $this->assertEquals(
            "/**\n     * @return void\n     */",
            $rflClass->getMethod("fooWithComment")->getDocComment()
        );
    }

    public function testStubbingVariadics()
    {
        $mock = Phake::mock('PhakeTest_Variadic');

        Phake::when($mock)->variadicMethod->thenCallParent();

        $this->assertEquals(array(1,2,3,4), $mock->variadicMethod(1, 2, 3, 4));
    }

    public function testMockingVariadics()
    {
        $mock = Phake::mock('PhakeTest_Variadic');

        $mock->variadicMethod(1, 2, 3, 4, 5, 6);

        Phake::verify($mock)->variadicMethod(1, 2, 3, 4, 5, 6);
    }

    public function testStubbingScalarReturnHints()
    {
        $mock = Phake::mock('PhakeTest_ScalarTypes');

        Phake::when($mock)->scalarHints->thenReturn(2);

        $this->assertEquals(2, $mock->scalarHints(1, 1));
    }

    public function testStubbingScalarReturnsWrongType()
    {
        $mock = Phake::mock('PhakeTest_ScalarTypes');

        Phake::when($mock)->scalarHints->thenReturn(array());

        try {
            $this->assertEquals(array(), $mock->scalarHints(1, 1));
        } catch (\TypeError $e) {
            Phake::verify($mock)->scalarHints(1, 1);
            return;
        } catch (\Throwable $e) {
            $this->fail("Expected A Type Error, instead got " . get_class($e) . " {$e}");
        }
        $this->fail("Expected A Type Error, no error received");
    }

    public function testDefaultStubChanged()
    {
        $mock = Phake::mock('PhakeTest_ScalarTypes');

        $mock->scalarHints(1, 1);

        Phake::verify($mock)->scalarHints(1, 1);
    }

    public function testVoidStubReturnsProperly()
    {
        $mock = Phake::mock('PhakeTest_VoidType');

        $this->assertNull($mock->voidMethod());

        Phake::verify($mock)->voidMethod();
    }

    public function testVoidStubThrowsException()
    {
        $mock = Phake::mock('PhakeTest_VoidType');

        $expectedException = new \Exception("Test Exception");
        Phake::when($mock)->voidMethod->thenThrow($expectedException);

        try {
            $mock->voidMethod();
            $this->fail("The mocked void method did not throw an exception");
        } catch (\Exception $actualException) {
            $this->assertSame($expectedException, $actualException, "The same exception was not thrown");
        }
    }

    public function testVoidStubCanCallParent()
    {
        $mock = Phake::mock('PhakeTest_VoidType');

        Phake::when($mock)->voidMethod->thenCallParent();

        $mock->voidMethod();

        $this->assertEquals(1, $mock->voidCallCount, "Void call count was not incremented, looks like callParent doesn't work");
    }

    public function testStubbingNotNullableReturnHint()
    {
        $mock = Phake::mock('PhakeTest_ScalarTypes');

        Phake::when($mock)->objectReturn->thenReturn(null);

        try {
            $mock->objectReturn();
            $this->fail('Expected TypeError');
        } catch (\TypeError $e) {
            $this->assertTrue(true);
        }
    }

    public function testStubbingNullableReturnHints()
    {
        $mock = Phake::mock('PhakeTest_NullableTypes');
        Phake::when($mock)->objectReturn->thenReturn(null);

        try {
            $this->assertSame(null, $mock->objectReturn());
        } catch (\TypeError $e) {
            $this->fail('Expected stubbing objectReturn null');
        }

        Phake::verify($mock, Phake::times(1))->objectReturn();
    }

    public function testStubbingNullableParameterHints()
    {
        $mock = Phake::mock('PhakeTest_NullableTypes');

        try {
            $mock->objectParameter(null);
        } catch (\TypeError $e) {
            $this->fail('Expected stubbing objectParameter to accept null');
        }

        Phake::verify($mock, Phake::times(1))->objectParameter(null);
    }

    public function testStubbingNullableReturnWrongType()
    {
        $mock = Phake::mock('PhakeTest_NullableTypes');
        Phake::when($mock)->objectReturn->thenReturn(array());

        try {
            $mock->objectReturn();
        } catch (\TypeError $e) {
            $this->assertTrue(true);
            return;
        } catch (\Throwable $e) {
            $this->fail("Expected A Type Error, instead got " . get_class($e) . " {$e}");
        }

        $this->fail("Expected A Type Error, no error received");
    }

    public function testDefaultReturnType()
    {
        $mock = Phake::mock('PhakeTest_NullableTypes');
        $this->assertTrue($mock->objectReturn() instanceof \PhakeTest_A);
    }

    public function testStubbingUnionTypes()
    {
        if (version_compare(phpversion(), '8.0.0') < 0) {
            $this->markTestSkipped('Union types are not supported in PHP versions prior to 8.0');
        }

        $this->assertInstanceOf('PhakeTest_UnionTypes', Phake::mock('PhakeTest_UnionTypes'));
    }

    public function testStubbingUnionParameterHints()
    {
        if (version_compare(phpversion(), '8.0.0') < 0) {
            $this->markTestSkipped('Union types are not supported in PHP versions prior to 8.0');
        }

        $mock = Phake::mock('PhakeTest_UnionTypes');

        try {
            $mock->unionParam(1);
            $mock->unionParam('foo');
        } catch (\TypeError $e) {
            $this->fail('Expected stubbing objectParameter to accept 1 and \'foo\'');
        }
        Phake::verify($mock, Phake::times(1))->unionParam(1);
        Phake::verify($mock, Phake::times(1))->unionParam('foo');
    }

    public function testStubbingUnionParameterHintsWrongType()
    {
        if (version_compare(phpversion(), '8.0.0') < 0) {
            $this->markTestSkipped('Union types are not supported in PHP versions prior to 8.0');
        }

        $mock = Phake::mock('PhakeTest_UnionTypes');

        try {
            $mock->unionParam(null);
        } catch (\TypeError $e) {
            $this->assertTrue(true);
            return true;
        }
        $this->fail('Expected TypeError');
    }

    public function testStubbingNullableUnionParameterHints()
    {
        if (version_compare(phpversion(), '8.0.0') < 0) {
            $this->markTestSkipped('Union types are not supported in PHP versions prior to 8.0');
        }

        $mock = Phake::mock('PhakeTest_UnionTypes');

        try {
            $mock->unionParamNullable(1);
            $mock->unionParamNullable('foo');
            $mock->unionParamNullable(null);
        } catch (\TypeError $e) {
            $this->fail('Expected stubbing objectParameter to accept 1, \'foo\', and null');
        }
        Phake::verify($mock, Phake::times(1))->unionParamNullable(1);
        Phake::verify($mock, Phake::times(1))->unionParamNullable('foo');
        Phake::verify($mock, Phake::times(1))->unionParamNullable(null);
    }

    public function testStubbingUnionReturnType()
    {
        if (version_compare(phpversion(), '8.0.0') < 0) {
            $this->markTestSkipped('Union types are not supported in PHP versions prior to 8.0');
        }

        $mock = Phake::mock('PhakeTest_UnionTypes');
        Phake::when($mock)->unionReturn()->thenReturn(1)->thenreturn('foo');

        try {
            $this->assertSame(1, $mock->unionReturn());
            $this->assertSame('foo', $mock->unionReturn());
        } catch (\TypeError $e) {
            $this->fail('Expected stubbing return 1 and \'foo\'');
        }
    }

    public function testStubbingUnionReturnWrongType()
    {
        if (version_compare(phpversion(), '8.0.0') < 0) {
            $this->markTestSkipped('Union types are not supported in PHP versions prior to 8.0');
        }

        $mock = Phake::mock('PhakeTest_UnionTypes');
        Phake::when($mock)->unionReturn()->thenReturn(null);

        try {
            $mock->unionReturn();
        } catch (\TypeError $e) {
            $this->assertTrue(true);
            return true;
        }
        $this->fail('Expected stubbing return 1 and \'foo\'');
    }

    public function testStubbingUnionReturnNullableType()
    {
        if (version_compare(phpversion(), '8.0.0') < 0) {
            $this->markTestSkipped('Union types are not supported in PHP versions prior to 8.0');
        }

        $mock = Phake::mock('PhakeTest_UnionTypes');
        Phake::when($mock)->unionReturnNullable()->thenReturn(1)->thenreturn('foo')->thenReturn(null);

        try {
            $this->assertSame(1, $mock->unionReturnNullable());
            $this->assertSame('foo', $mock->unionReturnNullable());
            $this->assertSame(null, $mock->unionReturnNullable());
        } catch (\TypeError $e) {
            $this->fail('Expected stubbing return 1 and \'foo\'');
        }
    }

    public function testStubbingUnionSelfParameterHints()
    {
        if (version_compare(phpversion(), '8.0.0') < 0) {
            $this->markTestSkipped('Union types are not supported in PHP versions prior to 8.0');
        }

        $mock = Phake::mock('PhakeTest_UnionTypes');

        try {
            $mock->unionParamWithSelf($mock);
        } catch (\TypeError $e) {
            $this->fail('Expected stubbing parameter to accept self');
        }

        Phake::verify($mock, Phake::times(1))->unionParamWithSelf($mock);
    }

    public function testStubbingUnionReturnWithSelf()
    {
        if (version_compare(phpversion(), '8.0.0') < 0) {
            $this->markTestSkipped('Union types are not supported in PHP versions prior to 8.0');
        }

        $mock = Phake::mock('PhakeTest_UnionTypes');
        Phake::when($mock)->unionReturnWithSelf()->thenReturnSelf();

        try {
            $this->assertSame($mock, $mock->unionReturnWithSelf());
        } catch (\TypeError $e) {
            $this->fail('Expected stubbing return self');
        }
    }
}
