<?php

declare(strict_types=1);

namespace PhpPg\PgProto3\Messages;

use PhpPg\PgProto3\Helper\BinaryStream;

class Sync implements FrontendMessageInterface
{
    use MessageName;

    public const TYPE = 'S';

    public function decode(string $data): void
    {
        // TODO: Implement decode() method.
    }

    public function encode(): string
    {
        $buffer = new BinaryStream();

        $buffer->writeByte(self::TYPE);
        $buffer->writeUInt32BE(4);

        return $buffer->getBuffer();
    }

    public function getType(): string
    {
        return self::TYPE;
    }
}