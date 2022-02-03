<?php

declare(strict_types=1);

namespace PhpPg\PgProto3\Messages;

class CopyInResponse extends AbstractCopyResponse
{
    use MessageName;

    public const TYPE = 'G';

    public function getType(): string
    {
        return self::TYPE;
    }
}
