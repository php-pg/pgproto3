<?php

declare(strict_types=1);

namespace PhpPg\PgProto3\Messages;

use PhpPg\PgProto3\Exception\InvalidMessageSizeException;
use PhpPg\PgProto3\Helper\BinaryStream;

class BackendKeyData implements BackendMessageInterface
{
    use MessageName;

    public const TYPE = 'K';

    public int $processId = 0;
    public int $secretKey = 0;

    public function decode(string $data): void
    {
        if (\strlen($data) !== 8) {
            throw new InvalidMessageSizeException($this->getName(), 8, \strlen($data));
        }

        $stream = new BinaryStream($data);

        $this->processId = $stream->readUInt32BE();
        $this->secretKey = $stream->readUInt32BE();
    }

    public function encode(): string
    {
        // TODO: Implement encode() method.
        return '';
    }

    public function getType(): string
    {
        return self::TYPE;
    }
}
