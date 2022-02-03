<?php

declare(strict_types=1);

namespace PhpPg\PgProto3\Messages;

use PhpPg\PgProto3\Helper\BinaryStream;

class GSSENCRequest implements FrontendMessageInterface
{
    use MessageName;

    public const TYPE = '';

    public function decode(string $data): void
    {
        // TODO: Implement decode() method.
    }

    public function encode(): string
    {
        $stream = new BinaryStream();
        $stream->writeUInt32BE(8);
        $stream->writeUInt32BE(80877104);

        return $stream->getBuffer();
    }

    public function getType(): string
    {
        return self::TYPE;
    }
}
