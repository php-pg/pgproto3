<?php

declare(strict_types=1);

namespace PhpPg\PgProto3\Messages;

use PhpPg\PgProto3\Exception\InvalidMessageSizeException;
use PhpPg\PgProto3\Helper\BinaryStream;

class EmptyQueryResponse implements BackendMessageInterface
{
    use MessageName;

    public const TYPE = 'I';

    public function decode(string $data): void
    {
        if ($data !== '') {
            throw new InvalidMessageSizeException($this->getName(), 0, \strlen($data));
        }
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
