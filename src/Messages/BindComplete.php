<?php

declare(strict_types=1);

namespace PhpPg\PgProto3\Messages;

use PhpPg\PgProto3\Exception\InvalidMessageSizeException;

class BindComplete implements BackendMessageInterface
{
    use MessageName;

    public const TYPE = '2';

    public function decode(string $data): void
    {
        if ($data !== '') {
            throw new InvalidMessageSizeException($this->getName(), 0, \strlen($data));
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