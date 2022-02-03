<?php

declare(strict_types=1);

namespace PhpPg\PgProto3\Messages;

class CopyOutResponse extends AbstractCopyResponse
{
    use MessageName;

    public const TYPE = 'H';

    public function getType(): string
    {
        return self::TYPE;
    }
}
