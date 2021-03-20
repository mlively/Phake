<?php declare(strict_types=1);

namespace Phake\Stubber\Answers;

/**
 * Allows providing a static answer to a stubbed method call.
 *
 * @author Mathieu Kooiman <mathieuk@gmail.com>
 */
class LambdaAnswer implements \Phake\Stubber\IAnswer
{
    /**
     * @var mixed
     */
    private $answerLambda;

    /**
     * @param mixed $answerLambda
     */
    public function __construct($answerLambda)
    {
        $this->answerLambda = $answerLambda;
    }

    /**
     * Nothing to process
     *
     * @param mixed $answer
     *
     * @return null
     */
    public function processAnswer($answer)
    {
    }

    public function getAnswerCallback($context, $method)
    {
        return $this->answerLambda;
    }
}


