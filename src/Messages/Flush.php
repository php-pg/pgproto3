<?php

declare(strict_types=1);

namespace PhpPg\PgProto3\Messages;

use PhpPg\PgProto3\Helper\BinaryStream;

class Flush implements FrontendMessageInterface
{
    use MessageName;

    public const TYPE = 'H';

    public function encode(): string
    {
        $stream = new BinaryStream();

        $stream->writeByte(self::TYPE);
        $stream->writeUInt32BE(0);

        return $stream->getBuffer();
    }

    public function decode(string $data): void
    {
        // TODO: Implement decode() method.
    }

    public function getType(): string
    {
        return self::TYPE;
    }
}
