<?php

declare(strict_types=1);

namespace PhpPg\PgProto3\Messages;

class ErrorResponse extends AbstractErrorResponse
{
    use MessageName;

    public const TYPE = 'E';

    public function getType(): string
    {
        return self::TYPE;
    }
}
