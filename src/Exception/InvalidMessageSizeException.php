<?php

declare(strict_types=1);

namespace PhpPg\PgProto3\Exception;

class InvalidMessageSizeException extends ProtoException
{
    public function __construct(string $messageType, int $exceptedLen, int $actualLen)
    {
        parent::__construct(
            \sprintf("%s body must have length of %d, but it is %d", $messageType, $exceptedLen, $actualLen),
            0,
            null
        );
    }
}