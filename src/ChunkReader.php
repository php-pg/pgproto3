<?php

declare(strict_types=1);

namespace PhpPg\PgProto3;

use Amp\ByteStream\ClosedException;
use Amp\ByteStream\ReadableStream;
use Amp\Cancellation;
use Amp\CancelledException;
use InvalidArgumentException;

use function strlen;
use function substr;

class ChunkReader implements ChunkReaderInterface
{
    private string $buffer = '';
    private int $readPos = 0;
    private int $writePos = 0;

    public function __construct(
        private ReadableStream $stream,
        private int $minBufferSize = 8192,
    ) {
    }

    /**
     * @param Cancellation|null $cancellation
     * @param int|null $n throws InvalidArgumentException when $n < 1
     * @return string
     *
     * @throws ClosedException
     * @throws CancelledException
     */
    public function read(?Cancellation $cancellation = null, ?int $n = null): string
    {
        if ($n === null || $n < 1) {
            throw new InvalidArgumentException('n must be greater than 0');
        }

        $availBytesToRead = $this->getAvailableBytesToRead();

        // n bytes are already in buffer
        if ($availBytesToRead >= $n) {
            $buf = substr($this->buffer, $this->readPos, $n);
            $this->readPos += $n;

            return $buf;
        }

        $minReadCount = $n - $availBytesToRead;

        $this->appendAtLeast($minReadCount, $cancellation);

        $buf = substr($this->buffer, $this->readPos, $n);
        $this->readPos += $n;

        // Cut buffer
        if (strlen($this->buffer) > $this->minBufferSize) {
            $this->buffer = substr($this->buffer, $this->readPos, $this->getAvailableBytesToRead());
            $this->readPos = 0;
            $this->writePos = strlen($this->buffer);
        }

        return $buf;
    }

    public function close(): void
    {
        $this->stream->isClosed();
    }

    public function isClosed(): bool
    {
        return $this->stream->isClosed();
    }

    public function isReadable(): bool
    {
        return $this->stream->isReadable();
    }

    private function getAvailableBytesToRead(): int
    {
        return $this->writePos - $this->readPos;
    }

    /**
     * @param int $n
     * @param Cancellation|null $cancellation
     * @return void
     *
     * @throws ClosedException
     * @throws CancelledException
     */
    private function appendAtLeast(int $n, ?Cancellation $cancellation): void
    {
        $readLen = 0;

        while ($readLen < $n) {
            $data = $this->stream->read($cancellation);
            if ($data === null) {
                throw new ClosedException('The stream closed before the given number of bytes were read');
            }

            $dataLen = strlen($data);
            $readLen += $dataLen;

            $this->buffer .= $data;
            $this->writePos += $dataLen;
        }
    }
}
