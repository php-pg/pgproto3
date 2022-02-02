<?php

declare(strict_types=1);

namespace PhpPg\PgProto3\Messages;

use PhpPg\PgProto3\Exception\InvalidMessageFormatException;
use PhpPg\PgProto3\Helper\BinaryStream;

abstract class AbstractCopyResponse implements BackendMessageInterface
{
    /**
     * 0 indicates the overall COPY format is textual (rows separated by newlines,
     * columns separated by separator characters, etc).
     *
     * 1 indicates the overall COPY format is binary (similar to DataRow format). See COPY for more information.
     */
    public int $overallFormat = 0;

    /**
     * The format codes to be used for each column. Each must presently be zero (text) or one (binary).
     * All must be zero if the overall copy format is textual.
     *
     * @var array<int>
     */
    public array $columnFormatCodes = [];

    public function decode(string $data): void
    {
        if (\strlen($data) < 3) {
            throw new InvalidMessageFormatException($this->getName());
        }

        $stream = new BinaryStream($data);

        $this->overallFormat = $stream->readUInt8();

        $cnt = $stream->readUInt16BE();

        try {
            for ($i = 0; $i < $cnt; $i++) {
                $this->columnFormatCodes[] = $stream->readUInt16BE();
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
}