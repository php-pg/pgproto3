<?php

declare(strict_types=1);

namespace PhpPg\PgProto3\Messages;

use PhpPg\PgProto3\Exception\InvalidMessageFormatException;
use PhpPg\PgProto3\Helper\BinaryStream;

class FunctionCallResponse implements BackendMessageInterface
{
    use MessageName;

    public const TYPE = 'V';

    public function __construct(
        public ?string $result = null,
    ) {
    }

    public function getType(): string
    {
        return self::TYPE;
    }

    public function encode(): string
    {
        // TODO: Implement encode() method.
        return '';
    }

    public function decode(string $data): void
    {
        if (\strlen($data) < 4) {
            throw new InvalidMessageFormatException($this->getName());
        }

        $stream = new BinaryStream($data);
        $len = $stream->readUInt32BE();

        /**
         * The length of the function result value, in bytes (this count does not include itself).
         * Can be zero.
         * As a special case, -1 indicates a NULL function result.
         * No value bytes follow in the NULL case.
         */

        if ($len === 0xFFFFFFFF) {
            $this->result = null;
            return;
        }

        try {
            $this->result = $stream->readString($len);
        } catch (\OutOfBoundsException $e) {
            throw new InvalidMessageFormatException($this->getName(), $e);
        }
    }
}