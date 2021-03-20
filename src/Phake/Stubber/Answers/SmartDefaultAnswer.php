<?php declare(strict_types=1);

namespace Phake\Stubber\Answers;
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
 * Returns the proper default value for a method based on the return type.
 *
 * @author Mike Lively <m@digitalsandwich.com>
 */
class SmartDefaultAnswer implements \Phake\Stubber\IAnswer
{
    public function processAnswer($answer)
    {

    }

    public function getAnswerCallback($context, $method)
    {
        $class = new \ReflectionClass($context);
        $method = $class->getMethod($method);

        $defaultAnswer = null;

        if ($method->hasReturnType())
        {
            $returnType = $method->getReturnType();
            $typeNames = $returnType instanceof \ReflectionNamedType ? [ $returnType->getName() ] : array_map(function($t) { return $t->getName(); }, $returnType->getTypes());
            foreach ($typeNames as $typeName) {
                switch ($typeName)
                {
                    case 'int':
                        $defaultAnswer = 0;
                        break 2;
                    case 'float':
                        $defaultAnswer = 0.0;
                        break 2;
                    case 'string':
                        $defaultAnswer = "";
                        break 2;
                    case 'bool':
                    case 'false':
                        $defaultAnswer = false;
                        break 2;
                    case 'array':
                        $defaultAnswer = array();
                        break 2;
                    case 'callable':
                        $defaultAnswer = function () {};
                        break 2;
                    case 'self':
                        $defaultAnswer = \Phake::mock($method->getDeclaringClass()->getName());
                        break 2;
                    default:
                        if (class_exists($typeName))
                        {
                            $defaultAnswer = \Phake::mock($typeName);
                            break 2;
                        }
                        break;
                }
            }
        }

        return function () use ($defaultAnswer)
        {
            return $defaultAnswer;
        };
    }
}


