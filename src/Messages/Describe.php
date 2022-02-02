<?php

declare(strict_types=1);

namespace PhpPg\PgProto3\Messages;

use PhpPg\PgProto3\Helper\BinaryStream;

class Describe implements FrontendMessageInterface
{
    use MessageName;

    public const TYPE = 'D';

    public function __construct(
        /** 'S' = prepared statement, 'P' = portal */
        public string $objectType = '',

        public string $name = '',
    ) {
    }

    public function decode(string $data): void
    {
        // TODO: Implement decode() method.
    }

    public function encode(): string
    {
        $stream = new BinaryStream();
        $stream->writeByte(self::TYPE);
        $stream->writeUInt32BE(0);

        $stream->writeByte($this->objectType);
        $stream->writeCString($this->name);

        $stream->setUInt32BE($stream->getSize() - 1, 1);

        return $stream->getBuffer();
    }

    public function getType(): string
    {
        return self::TYPE;
    }
}