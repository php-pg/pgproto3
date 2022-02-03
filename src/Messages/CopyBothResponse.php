<?php

declare(strict_types=1);

namespace PhpPg\PgProto3\Messages;

class CopyBothResponse extends AbstractCopyResponse
{
    use MessageName;

    public const TYPE = 'W';

    public function getType(): string
    {
        return self::TYPE;
    }
}
