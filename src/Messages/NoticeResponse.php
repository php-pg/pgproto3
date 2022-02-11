<?php

declare(strict_types=1);

namespace PhpPg\PgProto3\Messages;

class NoticeResponse extends AbstractErrorResponse
{
    use MessageName;

    public const TYPE = 'N';

    public function getType(): string
    {
        return self::TYPE;
    }
}
