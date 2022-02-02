<?php

declare(strict_types=1);

namespace PhpPg\PgProto3\Messages;

use PhpPg\PgProto3\Exception\InvalidMessageFormatException;
use PhpPg\PgProto3\Helper\BinaryStream;

class NotificationResponse implements BackendMessageInterface
{
    use MessageName;

    public const TYPE = 'A';

    public int $pid;
    public string $channel;
    public string $payload;

    public function decode(string $data): void
    {
        $stream = new BinaryStream($data);

        try {
            $this->pid = $stream->readUInt32BE();
            $this->channel = $stream->readCString();
            $this->payload = $stream->readCString();
        } catch (\OutOfBoundsException $e) {
            throw new InvalidMessageFormatException($this->getName(), $e);
        }
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