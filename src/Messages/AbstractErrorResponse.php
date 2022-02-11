<?php

declare(strict_types=1);

namespace PhpPg\PgProto3\Messages;

use PhpPg\PgProto3\Exception\InvalidMessageFormatException;
use PhpPg\PgProto3\Helper\BinaryStream;

abstract class AbstractErrorResponse implements BackendMessageInterface
{
    public function __construct(
        public string $severity = '',
        public string $severityNonLocalized = '',
        public string $code = '',
        public string $message = '',
        public string $detail = '',
        public string $hint = '',
        public int $position = 0,
        public int $internalPosition = 0,
        public string $internalQuery = '',
        public string $where = '',
        public string $schemaName = '',
        public string $tableName = '',
        public string $columnName = '',
        public string $dataTypeName = '',
        public string $constraintName = '',
        public string $file = '',
        public int $line = 0,
        public string $routine = '',
    ) {
    }

    /**
     * @var array<string,string>
     */
    public array $unknownFields = [];

    protected function reset(): void
    {
        $this->severity = '';
        $this->severityNonLocalized = '';
        $this->code = '';
        $this->message = '';
        $this->detail = '';
        $this->hint = '';
        $this->position = 0;
        $this->internalPosition = 0;
        $this->internalQuery = '';
        $this->where = '';
        $this->schemaName = '';
        $this->tableName = '';
        $this->columnName = '';
        $this->dataTypeName = '';
        $this->constraintName = '';
        $this->file = '';
        $this->line = 0;
        $this->routine = '';
        $this->unknownFields = [];
    }

    public function decode(string $data): void
    {
        $this->reset();
        $stream = new BinaryStream($data);

        try {
            while (!$stream->eof()) {
                $key = $stream->readByte();
                // if zero, this is the message terminator and no string follows.
                if ($key === "\0") {
                    break;
                }

                $value = $stream->readCString();

                match ($key) {
                    'S' => $this->severity = $value,
                    'V' => $this->severityNonLocalized = $value,
                    'C' => $this->code = $value,
                    'M' => $this->message = $value,
                    'D' => $this->detail = $value,
                    'H' => $this->hint = $value,
                    'P' => $this->position = (int)$value,
                    'p' => $this->internalPosition = (int)$value,
                    'q' => $this->internalQuery = $value,
                    'W' => $this->where = $value,
                    's' => $this->schemaName = $value,
                    't' => $this->tableName = $value,
                    'c' => $this->columnName = $value,
                    'd' => $this->dataTypeName = $value,
                    'n' => $this->constraintName = $value,
                    'F' => $this->file = $value,
                    'L' => $this->line = (int)$value,
                    'R' => $this->routine = $value,
                    default => $this->unknownFields[$key] = $value,
                };
            }
        } catch (\OutOfBoundsException $e) {
            throw new InvalidMessageFormatException($this->getName(), $e);
        }
    }

    public function getSeverity(): string
    {
        if ($this->severityNonLocalized !== '') {
            return $this->severityNonLocalized;
        }

        return $this->severity;
    }

    public function encode(): string
    {
        // TODO: Implement encode() method.
        return '';
    }
}
