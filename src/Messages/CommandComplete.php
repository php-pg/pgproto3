<?php

declare(strict_types=1);

namespace PhpPg\PgProto3\Messages;

use PhpPg\PgProto3\Exception\InvalidMessageFormatException;
use PhpPg\PgProto3\Helper\BinaryStream;

class CommandComplete implements BackendMessageInterface
{
    use MessageName;

    public const TYPE = 'C';

    public function __construct(public string $commandTag = '')
    {
    }

    public function decode(string $data): void
    {
        $stream = new BinaryStream($data);

        try {
            $this->commandTag = $stream->readCString();
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
