<?php

declare(strict_types=1);

namespace PhpPg\PgProto3\Messages;

use PhpPg\PgProto3\Exception\InvalidMessageSizeException;
use PhpPg\PgProto3\Helper\BinaryStream;

class CopyDone implements FrontendMessageInterface, BackendMessageInterface
{
    use MessageName;

    public const TYPE = 'c';

    public function decode(string $data): void
    {
        if ($data !== '') {
            throw new InvalidMessageSizeException($this->getName(), 0, \strlen($data));
        }
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