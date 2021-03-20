<?php declare(strict_types=1);

namespace Phake;

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
 * Tests the facade class for Phake
 *
 * @author Mike Lively <m@digitalsandwich.com>
 */
class FacadeTest extends TestCase
{
    /**
     * @var Facade
     */
    private $facade;

    /**
     * @var Phake\ClassGenerator\MockClass
     */
    private $mockGenerator;

    /**
     * @Mock
     * @var Phake\Mock\InfoRegistry
     */
    private $infoRegistry;

    /**
     * Sets up the mock generator
     */
    public function setUp(): void
    {
        Phake::initAnnotations($this);
        $this->mockGenerator = $this->getMockBuilder(Phake\ClassGenerator\MockClass::class)->getMock();
        $this->facade        = new Facade($this->infoRegistry);
    }

    /**
     * Tests that the mock generator is called properly
     */
    public function testMock()
    {
        $mockedClass   = 'stdClass';
        $mockGenerator = $this->getMockBuilder(Phake\ClassGenerator\MockClass::class)->getMock();

        $this->setMockGeneratorExpectations($mockedClass, $mockGenerator);

        $this->facade->mock(
            $mockedClass,
            $mockGenerator,
            $this->getMockBuilder(Phake\CallRecorder\Recorder::class)->getMock(),
            $this->getMockBuilder(Phake\Stubber\IAnswer::class)->getMock()
        );
    }

    /**
     * Tests that the mock generator is called properly
     */
    public function testMockInterface()
    {
        $mockedClass   = 'PhakeTest_MockedInterface';
        $mockGenerator = $this->getMockBuilder(Phake\ClassGenerator\MockClass::class)->getMock();

        $this->setMockGeneratorExpectations($mockedClass, $mockGenerator);

        $this->facade->mock(
            $mockedClass,
            $mockGenerator,
            $this->getMockBuilder(Phake\CallRecorder\Recorder::class)->getMock(),
            $this->getMockBuilder(Phake\Stubber\IAnswer::class)->getMock()
        );
    }

    /**
     * Tests that the mock generator will fail when given a class that does not exist.
     */
    public function testMockThrowsOnNonExistantClass()
    {
        $this->expectException(\InvalidArgumentException::class);

        $mockedClass = 'NonExistantClass';

        $this->facade->mock(
            $mockedClass,
            $this->getMockBuilder(Phake\ClassGenerator\MockClass::class)->getMock(),
            $this->getMockBuilder(Phake\CallRecorder\Recorder::class)->getMock(),
            $this->getMockBuilder(Phake\Stubber\IAnswer::class)->getMock()
        );
    }

    /**
     * Tests that Phake will pass necessary components to a generated class when instantiating it.
     */
    public function testMockPassesNecessaryComponentsToInstantiatedClass()
    {
        $mockedClass = 'stdClass';

        $recorder       = $this->getMockBuilder(Phake\CallRecorder\Recorder::class)->getMock();
        $classGenerator = $this->getMockBuilder(Phake\ClassGenerator\MockClass::class)->getMock();
        $answer         = $this->getMockBuilder(Phake\Stubber\IAnswer::class)->getMock();


        $this->setMockInstantiatorExpectations($classGenerator, $recorder, $answer);

        $this->facade->mock($mockedClass, $classGenerator, $recorder, $answer);
    }

    /**
     * Test that autoload doesn't get called on generated classes
     */
    public function testAutoLoadNotCalledOnMock()
    {
        spl_autoload_register(array(__CLASS__, 'autoload'));
        try {
            $mockedClass   = 'stdClass';
            $mockGenerator = $this->getMockBuilder(Phake\ClassGenerator\MockClass::class)->getMock();

            $mockGenerator->expects($this->once())
                ->method('instantiate')
                ->withAnyParameters();

            //This test will fail if the autoload below is called
            $this->facade->mock(
                $mockedClass,
                $mockGenerator,
                $this->getMockBuilder(Phake\CallRecorder\Recorder::class)->getMock(),
                $this->getMockBuilder(Phake\Stubber\IAnswer::class)->getMock()
            );

            spl_autoload_unregister(array(__CLASS__, 'autoload'));
        }
        catch (Exception $e)
        {
            spl_autoload_unregister(array(__CLASS__, 'autoload'));
            throw $e;
        }
    }

    /**
     * An autoload function that should never be called
     */
    public static function autoload()
    {
        $e = new Exception;
        self::fail("The autoloader should not be called: \n{$e->getTraceAsString()}");
    }

    public function testReset()
    {
        $this->facade->resetStaticInfo();

        Phake::verify($this->infoRegistry)->resetAll();
    }

    /**
     * Sets expectations for how the generator should be called
     *
     * @param string                         $mockedClass - The class name that we expect to mock
     * @param Phake\ClassGenerator\MockClass $mockGenerator
     */
    private function setMockGeneratorExpectations($mockedClass, Phake\ClassGenerator\MockClass $mockGenerator)
    {
        $mockGenerator->expects($this->once())
            ->method('generate')
            ->with($this->matchesRegularExpression('#^[A-Za-z0-9_]+$#'), $this->equalTo((array)$mockedClass), $this->equalTo($this->infoRegistry));
    }

    /**
     * Sets expectations for how the mock class should be created from the class generator
     *
     * @param Phake\ClassGenerator\MockClass $mockGenerator
     * @param Phake\CallRecorder\Recorder    $recorder
     * @param Phake\Stubber\IAnswer          $answer
     */
    private function setMockInstantiatorExpectations(
        Phake\ClassGenerator\MockClass $mockGenerator,
        Phake\CallRecorder\Recorder $recorder,
        Phake\Stubber\IAnswer $answer
    ) {
        $mockGenerator->expects($this->once())
            ->method('instantiate')
            ->with(
                $this->matchesRegularExpression('#^[A-Za-z0-9_]+$#'),
                $this->equalTo($recorder),
                $this->isInstanceOf(Phake\Stubber\StubMapper::class),
                $this->equalTo($answer)
            );
    }
}


