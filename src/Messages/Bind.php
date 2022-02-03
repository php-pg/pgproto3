<?php

declare(strict_types=1);

namespace PhpPg\PgProto3\Messages;

use PhpPg\PgProto3\Helper\BinaryStream;

class Bind implements FrontendMessageInterface
{
    use MessageName;

    public const TYPE = 'B';

    /**
     * @param string $destinationPortal
     * @param string $preparedStatement
     * @param array<int> $parameterFormatCodes int16
     * @param array<?string> $parameters
     * @param array<int> $resultFormatCodes int16
     */
    public function __construct(
        public string $destinationPortal = '',
        public string $preparedStatement = '',
        public array $parameterFormatCodes = [],
        public array $parameters = [],
        public array $resultFormatCodes = [],
    ) {
    }

    public function decode(string $data): void
    {
        // TODO: Implement decode() method.
    }

    public function encode(): string
    {
        $buffer = new BinaryStream();
        $buffer->writeByte(self::TYPE);

        // message size
        $buffer->writeUInt32BE(0);

        $buffer->writeCString($this->destinationPortal);
        $buffer->writeCString($this->preparedStatement);

        $buffer->writeUInt16BE(\count($this->parameterFormatCodes));
        foreach ($this->parameterFormatCodes as $parameterFormatCode) {
            $buffer->writeUInt16BE($parameterFormatCode);
        }

        $buffer->writeUInt16BE(\count($this->parameters));
        foreach ($this->parameters as $parameter) {
            if ($parameter === null) {
                // PostgreSQL representation of null value is -1 (int32)
                $buffer->writeUInt32BE(0xFFFFFFFF);
                continue;
            }

            // parameter length
            $buffer->writeUInt32BE(\strlen($parameter));
            // parameter value
            $buffer->writeString($parameter);
        }

        $buffer->writeUInt16BE(\count($this->resultFormatCodes));
        foreach ($this->resultFormatCodes as $resultFormatCode) {
            $buffer->writeUInt16BE($resultFormatCode);
        }

        $buffer->setUInt32BE($buffer->getSize() - 1, 1);

        return $buffer->getBuffer();
    }

    public function getType(): string
    {
        return self::TYPE;
    }
}
