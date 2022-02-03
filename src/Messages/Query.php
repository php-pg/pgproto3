<?php

declare(strict_types=1);

namespace PhpPg\PgProto3\Messages;

use PhpPg\PgProto3\Helper\BinaryStream;

class Query implements FrontendMessageInterface
{
    use MessageName;

    public const TYPE = 'Q';

    public function __construct(
        public string $query = '',
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
        $stream->writeUInt32BE(4 + \strlen($this->query) + 1);
        $stream->writeCString($this->query);

        return $stream->getBuffer();
    }

    public function getType(): string
    {
        return self::TYPE;
    }
}
