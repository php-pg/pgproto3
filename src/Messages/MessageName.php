<?php

declare(strict_types=1);

namespace PhpPg\PgProto3\Messages;

trait MessageName
{
    public function getName(): string
    {
        static $name = null;

        return $name ??= (new \ReflectionClass($this))->getShortName();
    }
}