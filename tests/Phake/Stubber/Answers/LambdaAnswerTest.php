<?php declare(strict_types=1);

namespace Phake\Stubber\Answers;

use PHPUnit\Framework\TestCase;

class LambdaAnswerTest extends TestCase
{
    public function testLambdaAnswerAcceptsOldschoolLambda()
    {
        $func   = function ($arg1) { return $arg1; };
        $answer = new LambdaAnswer($func);
        $result = call_user_func($answer->getAnswerCallback('someObject', 'testMethod'), 'bar');
        $this->assertSame("bar", $result);
    }
}
