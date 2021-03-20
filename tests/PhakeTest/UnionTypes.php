<?php

declare(strict_types=1);

class PhakeTest_UnionTypes
{
    public function unionParam(int|string $param) {

    }

    public function unionReturn(): int|string {

    }

    public function unionParamNullable(null|int|string $param) {

    }

    public function unionReturnNullable(): null|int|string {

    }

    public function unionParamWithSelf(self|false $me) {

    }

    public function unionReturnWithSelf(): self|false {

    }
}
