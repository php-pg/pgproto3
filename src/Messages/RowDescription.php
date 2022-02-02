<?php

declare(strict_types=1);

namespace PhpPg\PgProto3\Messages;

use PhpPg\PgProto3\Exception\InvalidMessageFormatException;
use PhpPg\PgProto3\Helper\BinaryStream;

class RowDescription implements BackendMessageInterface
{
    use MessageName;

    public const TYPE = 'T';

    /**
     * @var array<FieldDescription>
     */
    public array $fields = [];

    public function decode(string $data): void
    {
        $this->fields = [];

        if (\strlen($data) < 2) {
            throw new InvalidMessageFormatException($this->getName());
        }

        $buffer = new BinaryStream($data);

        $fieldCount = $buffer->readUInt16BE();

        try {
            for ($i = 0; $i < $fieldCount; $i++) {
                $name = $buffer->readCString();
                $tableOID = $buffer->readUInt32BE();
                $tableAttributeNumber = $buffer->readUInt16BE();
                $dataTypeOID = $buffer->readUInt32BE();
                $dataTypeSize = $buffer->readUInt16BE();
                $typeModifier = $buffer->readUInt32BE();
                $format = $buffer->readUInt16BE();

                $this->fields[] = new FieldDescription(
                    name: $name,
                    tableOID: $tableOID,
                    tableAttributeNumber: $tableAttributeNumber,
                    dataTypeOID: $dataTypeOID,
                    dataTypeSize: $dataTypeSize,
                    typeModifier: $typeModifier,
                    format: $format,
                );
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