<?php

declare(strict_types=1);

namespace PhpPg\PgProto3\Messages;

use PhpPg\PgProto3\Helper\BinaryStream;

class SASLResponse implements FrontendMessageInterface
{
    use MessageName;

    public const TYPE = 'p';

    public string $data = '';

    /**
     * @param string $data
     */
    public function __construct(string $data)
    {
        $this->data = $data;
    }

    public function decode(string $data): void
    {
        // TODO: Implement decode() method.
    }

    public function encode(): string
    {
        $stream = new BinaryStream();

        $stream->writeByte(self::TYPE);
        $stream->writeUInt32BE(4 + \strlen($this->data));

        $stream->writeString($this->data);

        return $stream->getBuffer();
    }

    public function getType(): string
    {
        return self::TYPE;
    }
}
