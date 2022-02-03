<?php

declare(strict_types=1);

namespace PhpPg\PgProto3\Exception;

class InvalidMessageFormatException extends ProtoException
{
    public function __construct(string $messageType, ?\Throwable $previous = null)
    {
        parent::__construct(
            \sprintf("%s body is invalid", $messageType),
            0,
            $previous,
        );
    }
}
