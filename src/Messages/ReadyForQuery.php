<?php

declare(strict_types=1);

namespace PhpPg\PgProto3\Messages;

use PhpPg\PgProto3\Exception\InvalidMessageSizeException;

class ReadyForQuery implements BackendMessageInterface
{
    use MessageName;

    public const TYPE = 'Z';

    public string $txStatus = '';

    public function decode(string $data): void
    {
        if (\strlen($data) !== 1) {
            throw new InvalidMessageSizeException($this->getName(), 1, \strlen($data));
        }

        $this->txStatus = $data[0];
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
