<?php
/* 
 * Phake - Mocking Framework
 * 
 * Copyright (c) 2010-2011, Mike Lively <m@digitalsandwich.com>
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

require_once 'Phake/CallRecorder/Call.php';
require_once 'Phake/IMock.php';

/**
 * Creates and executes the code necessary to create a mock class.
 *
 * @author Mike Lively <m@digitalsandwich.com>
 */
class Phake_ClassGenerator_MockClass
{
	/**
	 * Generates a new class with the given class name
	 *
	 * @param string $newClassName - The name of the new class
	 * @param string $mockedClassName - The name of the class being mocked
	 * @return NULL
	 */
	public function generate($newClassName, $mockedClassName)
	{
		if (class_exists($mockedClassName, TRUE))
		{
			$extends = "extends {$mockedClassName}";
			$implements = '';
		}
		elseif (interface_exists($mockedClassName, TRUE))
		{
			$extends = '';
			$implements = ", {$mockedClassName}";
		}

		$mockedClass = new ReflectionClass($mockedClassName);
		
		$classDef = file_get_contents(dirname(__FILE__) . '/Template/mock_class.tmpl');
		$classDef = str_replace(
				array(
					'{newClassName}',
					'{extends}',
					'{implements}',
					'{constructorChain}',
					'{mockedClassName}',
					'{mockedMethods}'
				), array(
					$newClassName,
					$extends,
					$implements,
					$this->getConstructorChaining($mockedClass),
					$mockedClassName,
					$this->generateMockedMethods($mockedClass)
				), $classDef);

		eval($classDef);
	}

	/**
	 * Instantiates a new instance of the given mocked class.
	 *
	 * @param string $newClassName
	 * @param Phake_CallRecorder_Recorder $recorder
	 * @param Phake_Stubber_StubMapper $mapper
	 * @param Phake_Stubber_IAnswer $defaultAnswer
	 * @param array $constructorArgs
	 * @return Phake_IMock of type $newClassName
	 */
	public function instantiate($newClassName, Phake_CallRecorder_Recorder $recorder, Phake_Stubber_StubMapper $mapper, Phake_Stubber_IAnswer $defaultAnswer, array $constructorArgs = null)
	{
		return new $newClassName($recorder, $mapper, $defaultAnswer, $constructorArgs);
	}

	/**
	 * Generate mock implementations of all public and protected methods in the mocked class.
	 * @param ReflectionClass $mockedClass
	 * @return string
	 */
	protected function generateMockedMethods(ReflectionClass $mockedClass)
	{
		$methodDefs = '';
		$filter = ReflectionMethod::IS_ABSTRACT | ReflectionMethod::IS_PROTECTED | ReflectionMethod::IS_PUBLIC | ~ReflectionMethod::IS_FINAL;
		foreach ($mockedClass->getMethods($filter) as $method)
		{
			if (!$method->isConstructor() && !$method->isDestructor() && !$method->isFinal())
			{
				if (strtolower($method->getName()) === '__call')
				{
					$methodDefs .= $this->implementCallMethod($method);
				}
				elseif (strtolower($method->getName()) === '__tostring')
				{
					$methodDefs .= $this->implementToStringMethod($method, $mockedClass->getName());
				}
				else
				{
					$methodDefs .= $this->implementMethod($method);
				}
				$methodDefs .= "\n";
			}
		}

		return $methodDefs;
	}

	/**
	 * Creates the constructor implementation
	 */
	protected function getConstructorChaining(ReflectionClass $originalClass)
	{
		return $originalClass->hasMethod('__construct') ? "

		if (is_array(\$constructorArgs))
		{
			call_user_func_array(array(\$this, 'parent::__construct'), \$constructorArgs);
		}
		" : "";
	}

	/**
	 * Creates the implementation of a single method
	 * @param ReflectionMethod $method
	 */
	protected function implementMethod(ReflectionMethod $method)
	{
		$modifiers = implode(' ', Reflection::getModifierNames($method->getModifiers() & ~ReflectionMethod::IS_ABSTRACT));
		
		$methodDef = file_get_contents(dirname(__FILE__) . '/Template/mock_method.tmpl');
		$methodDef = str_replace(
				array(
					'{modifiers}',
					'{methodName}',
					'{methodParameters}',
					'{copyMethodParameters}'
				), array(
					$modifiers,
					$method->getName(),
					$this->generateMethodParameters($method),
					$this->copyMethodParameters($method)
				), $methodDef);

		return $methodDef;
	}

	/**
	 * Creates the implementation of __call if necessary
	 * @param ReflectionMethod $method
	 */
	protected function implementCallMethod(ReflectionMethod $method)
	{
		$modifiers = implode(' ', Reflection::getModifierNames($method->getModifiers() & ~ReflectionMethod::IS_ABSTRACT));

		$methodDef = file_get_contents(dirname(__FILE__) . '/Template/mock_call_method.tmpl');
		
		$methodDef = str_replace(
				array(
					'{modifiers}',
					'{methodParameters}',
					'{copyMethodParameters}'
				), array(
					$modifiers,
					$this->generateMethodParameters($method),
					$this->copyMethodParameters($method)
				), $methodDef);
		
		return $methodDef;
	}

	/**
	 * Creates a mocked __toString() method if needed.
	 *
	 * Mocked objects with __toString() methods will currently ignore default answers, but can be stubbed to return any
	 * value as normal.
	 *
	 * @param ReflectionMethod $method
	 * @return string definition for the mocked __toString() method
	 */
	protected function implementToStringMethod(ReflectionMethod $method, $className)
	{
		$modifiers = implode(' ', Reflection::getModifierNames($method->getModifiers() & ~ReflectionMethod::IS_ABSTRACT));

		$methodDef = file_get_contents(dirname(__FILE__) . '/Template/mock_toString_method.tmpl');
		$methodDef = str_replace(
				array(
					'{modifiers}',
					'{methodParameters}',
					'{copyMethodParameters}',
					'{className}'
				), array(
					$modifiers,
					$this->generateMethodParameters($method),
					$this->copyMethodParameters($method),
					$className
				), $methodDef);
		
		return $methodDef;
	}

	/**
	 * Generates the code for all the parameters of a given method.
	 * @param ReflectionMethod $method
	 * @return string
	 */
	protected function generateMethodParameters(ReflectionMethod $method)
	{
		$parameters = array();
		foreach ($method->getParameters() as $parameter)
		{
			$parameters[] = $this->implementParameter($parameter);
		}

		return implode(', ', $parameters);
	}

	/**
	 * Generates the code for all the parameters of a given method.
	 * @param ReflectionMethod $method
	 * @return string
	 */
	protected function copyMethodParameters(ReflectionMethod $method)
	{
		$copies = "\$numArgs = count(func_get_args());\n";
		foreach ($method->getParameters() as $parameter)
		{
			$pos = $parameter->getPosition();
			$copies .= "if ({$pos} < \$numArgs) \$args[] =& \$parm{$pos};\n\t\t";
		}

		return $copies;
	}

	/**
	 * Generates the code for an individual method parameter.
	 * @param ReflectionParameter $parameter
	 * @return string
	 */
	protected function implementParameter(ReflectionParameter $parameter)
	{
		$default = '';
		$type = '';
		
		if ($parameter->isArray())
		{
			$type = 'array ';
		}
		elseif ($parameter->getClass() !== NULL)
		{
			$type = $parameter->getClass()->getName() . ' ';
		}

		if ($parameter->isDefaultValueAvailable())
		{
			$default = ' = ' . var_export($parameter->getDefaultValue(), TRUE);
		}
		elseif ($parameter->isOptional())
		{
			$default = ' = null';
		}

		return $type . ($parameter->isPassedByReference() ? '&' : '') . '$parm' . $parameter->getPosition() . $default;
	}
}

?>
