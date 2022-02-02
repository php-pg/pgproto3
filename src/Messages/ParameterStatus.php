<?php

declare(strict_types=1);

namespace PhpPg\PgProto3\Messages;

use PhpPg\PgProto3\Exception\InvalidMessageFormatException;
use PhpPg\PgProto3\Helper\BinaryStream;

class ParameterStatus implements BackendMessageInterface
{
    use MessageName;

    public const TYPE = 'S';

    public string $name = '';
    public string $value = '';

    public function decode(string $data): void
    {
        $stream = new BinaryStream($data);

        try {
            $this->name = $stream->readCString();
            $this->value = $stream->readCString();
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