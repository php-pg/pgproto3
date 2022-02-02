<?php

declare(strict_types=1);

namespace PhpPg\PgProto3\Messages;

use PhpPg\PgProto3\Exception\InvalidMessageFormatException;
use PhpPg\PgProto3\Helper\BinaryStream;

class DataRow implements BackendMessageInterface
{
    use MessageName;

    public const TYPE = 'D';

    /**
     * @var array<int, string|null>
     */
    public array $values = [];

    public function decode(string $data): void
    {
        $this->values = [];

        if (\strlen($data) < 2) {
            throw new InvalidMessageFormatException($this->getName());
        }

        $buffer = new BinaryStream($data);
        $fieldCount = $buffer->readUInt16BE();

        try {
            for ($i = 0; $i < $fieldCount; $i++) {
                $messageSize = $buffer->readUInt32BE();

                if ($messageSize === 0xFFFFFFFF) {
                    $this->values[$i] = null;
                } else {
                    $this->values[$i] = $buffer->readString($messageSize);
                }
            }
        } catch (\OutOfBoundsException $e) {
            throw new InvalidMessageFormatException($this->getName(), $e);
        }
    }

    public function encode(): string
    {
        // TODO
        return '';
    }

    public function getType(): string
    {
        return self::TYPE;
    }
}