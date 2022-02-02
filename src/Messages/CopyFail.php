<?php

declare(strict_types=1);

namespace PhpPg\PgProto3\Messages;

use PhpPg\PgProto3\Exception\InvalidMessageSizeException;
use PhpPg\PgProto3\Helper\BinaryStream;

class CopyFail implements FrontendMessageInterface
{
    use MessageName;

    public const TYPE = 'f';

    public function __construct(
        public string $message = '',
    ) {
    }

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
        $stream->writeUInt32BE(4 + \strlen($this->message) + 1);
        $stream->writeCString($this->message);

        return $stream->getBuffer();
    }

    public function getType(): string
    {
        return self::TYPE;
    }
}