<?php declare(strict_types=1);

namespace Phake\Proxies;
/* 
 * Phake - Mocking Framework
 * 
 * Copyright (c) 2010-2021, Mike Lively <mike.lively@sellingsource.com>
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
 * A proxy class to provide Stub Chaining through use of an AnswerCollection
 *
 * @author Mike Lively <m@digitalsandwich.com>
 */
class AnswerCollectionProxy implements \Phake\Stubber\IAnswerContainer, AnswerProxyInterface
{
    /**
     * @var \Phake\Stubber\AnswerCollection
     */
    private $collection;

    /**
     * @param \Phake\Stubber\AnswerCollection $collection
     */
    public function __construct(\Phake\Stubber\AnswerCollection $collection)
    {
        $this->collection = $collection;
    }

    /**
     * Binds a static answer to the method and object in the proxied binder.
     *
     * @param mixed $value
     *
     * @return AnswerCollectionProxy
     */
    public function thenReturn($value)
    {
        $this->collection->addAnswer(new \Phake\Stubber\Answers\StaticAnswer($value));
        return $this;
    }

    /**
     * Binds a Lambda answer to the method
     *
     * @param callback $value
     *
     * @throws \InvalidArgumentException
     * @return AnswerCollectionProxy
     */
    public function thenGetReturnByLambda($value)
    {
        if (!is_callable($value)) {
            throw new \InvalidArgumentException("Given lambda is not callable");
        }

        $this->collection->addAnswer(new \Phake\Stubber\Answers\LambdaAnswer($value));

        return $this;
    }

    /**
     * Binds a delegated call that will call a given method's parent.
     * @return AnswerCollectionProxy
     */
    public function thenCallParent()
    {
        $this->collection->addAnswer(new \Phake\Stubber\Answers\ParentDelegate());
        return $this;
    }

    /**
     * Binds an exception answer to the method and object in the proxied binder.
     *
     * @param \Exception $value
     *
     * @return AnswerCollectionProxy
     */
    public function thenThrow(\Exception $value)
    {
        $this->collection->addAnswer(new \Phake\Stubber\Answers\ExceptionAnswer($value));
        return $this;
    }

    /**
     * Binds a delegated call that will call a given method's parent while capturing that value to the passed in variable.
     *
     * @param mixed $captor
     *
     * @return AnswerCollectionProxy
     */
    public function captureReturnTo(&$captor)
    {
        $this->collection->addAnswer(new \Phake\Stubber\Answers\ParentDelegate($captor));
        return $this;
    }

    /**
     * Binds a callback answer to the method.
     *
     * @param callback $value
     *
     * @throws \InvalidArgumentException
     * @return \Phake\Stubber\IAnswerContainer
     */
    public function thenReturnCallback($value)
    {
        $this->collection->addAnswer(new \Phake\Stubber\Answers\LambdaAnswer($value));
        return $this;
    }

    public function thenDoNothing()
    {
        $this->collection->addAnswer(new \Phake\Stubber\Answers\NoAnswer());
        return $this;
    }

    public function thenReturnSelf()
    {
        $this->collection->addAnswer(new \Phake\Stubber\Answers\SelfAnswer());
        return $this;
    }

    /**
     * Returns an answer from the container
     * @return \Phake\Stubber\IAnswer
     */
    public function getAnswer()
    {
        return $this->collection->getAnswer();
    }
}
