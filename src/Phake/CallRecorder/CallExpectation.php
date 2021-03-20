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

/**
 * A call or set of calls that was expected
 */
class CallExpectation
{
    /**
     * @var \Phake\IMock
     */
    private $object;

    /**
     * @var string
     */
    private $method;

    /**
     * @var \Phake\Matchers\IChainableArgumentMatcher
     */
    private $argumentMatcher;

    /**
     * @var IVerifierMode
     */
    private $verifierMode;

    /**
     * @param \Phake\IMock|mixed $object
     * @param string $method
     * @param \Phake\Matchers\IChainableArgumentMatcher $argumentMatcher
     * @param IVerifierMode $verificationMode
     */
    public function __construct(
        $object,
        $method,
        \Phake\Matchers\IChainableArgumentMatcher $argumentMatcher = null,
        IVerifierMode $verificationMode
    ) {
        $this->object           = $object;
        $this->method           = $method;
        $this->argumentMatcher  = $argumentMatcher;
        $this->verifierMode     = $verificationMode;
    }

    /**
     * @return \Phake\IMock
     */
    public function getObject()
    {
        return $this->object;
    }

    /**
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @return \Phake\Matchers\IChainableArgumentMatcher
     */
    public function getArgumentMatcher()
    {
        return $this->argumentMatcher;
    }

    /**
     * @return IVerifierMode
     */
    public function getVerifierMode()
    {
        return $this->verifierMode;
    }

    public function __toString()
    {
        $arguments = array();

        $argumentMatcher = $this->argumentMatcher;

        while (!empty($argumentMatcher))
        {
            $arguments[] = $argumentMatcher->__toString();
            $argumentMatcher = $argumentMatcher->getNextMatcher();
        }

        $name = \Phake::getName($this->getObject());
        $access = is_string($this->object) ? '::' : '->';

        return "Expected {$name}{$access}{$this->getMethod()}(" . implode(
            ', ',
            $arguments
        ) . ") to be called {$this->getVerifierMode()->__toString()}";
    }
}
