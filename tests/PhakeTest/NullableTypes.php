<?php declare(strict_types=1);

class PhakeTest_NullableTypes
{
    public function objectReturn() : ?PhakeTest_A {

    }

    public function objectParameter(?PhakeTest_A $param) {

    }

    public function intReturn() : ?int {

    }

    public function intParameter(?int $param) {

    }

    public function floatReturn() : ?float {

    }

    public function floatParam(?float $param) {

    }

    public function stringReturn() : ?string {

    }

    public function stringParam(?string $param) {

    }

    public function boolReturn() : ?bool {

    }

    public function boolParam(?bool $param) {

    }

    public function arrayReturn() : ?array {

    }

    public function arrayParam(?array $param) {

    }

    public function callableReturn() : ?callable {

    }

    public function callableParam(?callable $param) {

    }
}
