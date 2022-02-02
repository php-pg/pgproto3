<?php

declare(strict_types=1);

namespace PhpPg\PgProto3\Messages;

use PhpPg\PgProto3\Helper\BinaryStream;

class Execute implements FrontendMessageInterface
{
    use MessageName;

    public const TYPE = 'E';

    public function __construct(
        public string $portal = '',
        public int $maxRows = 0,
    ) {
    }

    public function decode(string $data): void
    {
        // TODO: Implement decode() method.
    }

    public function encode(): string
    {
        $buffer = new BinaryStream();

        $buffer->writeByte(self::TYPE);
        // 4 - message size, strlen - size of portal in bytes, 1 - null-terminator, 4 - max rows
        $buffer->writeUInt32BE(4 + \strlen($this->portal) + 1 + 4);

        $buffer->writeCString($this->portal);
        $buffer->writeUInt32BE($this->maxRows);

        return $buffer->getBuffer();
    }

    public function getType(): string
    {
        return self::TYPE;
    }
}