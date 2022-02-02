<?php

declare(strict_types=1);

namespace PhpPg\PgProto3\Messages;

use PhpPg\PgProto3\Exception\InvalidMessageFormatException;
use PhpPg\PgProto3\Helper\BinaryStream;

class CommandComplete implements BackendMessageInterface
{
    use MessageName;

    public const TYPE = 'C';

    public CommandTag $commandTag;

    public function decode(string $data): void
    {
        $stream = new BinaryStream($data);

        try {
            $tag = $stream->readCString();
        } catch (\OutOfBoundsException $e) {
            throw new InvalidMessageFormatException($this->getName(), $e);
        }

        $this->commandTag = new CommandTag(
            tag: $tag,
        );
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