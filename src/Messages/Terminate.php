<?php

declare(strict_types=1);

namespace PhpPg\PgProto3\Messages;

use PhpPg\PgProto3\Helper\BinaryStream;

class Terminate implements FrontendMessageInterface
{
    use MessageName;

    public const TYPE = 'X';

    public function decode(string $data): void
    {
        // TODO: Implement decode() method.
    }

    public function encode(): string
    {
        $stream = new BinaryStream();
        $stream->writeByte(self::TYPE);
        $stream->writeUInt32BE(4);

        return $stream->getBuffer();
    }

    public function getType(): string
    {
        return self::TYPE;
    }
}