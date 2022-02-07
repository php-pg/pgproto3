<?php

declare(strict_types=1);

namespace PhpPg\PgProto3\Messages;

use PhpPg\PgProto3\Exception\InvalidMessageFormatException;
use PhpPg\PgProto3\Helper\BinaryStream;

class ParameterDescription implements BackendMessageInterface
{
    use MessageName;

    public const TYPE = 't';

    /**
     * @var array<int>
     */
    public array $parameterOIDs = [];

    public function decode(string $data): void
    {
        $this->parameterOIDs = [];

        if (\strlen($data) < 2) {
            throw new InvalidMessageFormatException($this->getName());
        }

        $buffer = new BinaryStream($data);

        try {
            // Reported parameter count will be incorrect when number of args is greater than uint16
            $buffer->readUInt16BE();

            // Instead, infer parameter count by remaining size of message
            $parameterCount = $buffer->getRemainingSize() / 4;
            if (!\is_int($parameterCount)) {
                throw new InvalidMessageFormatException($this->getName(), null);
            }

            for ($i = 0; $i < $parameterCount; $i++) {
                $this->parameterOIDs[] = $buffer->readUInt32BE();
            }
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
