<?php

declare(strict_types=1);

namespace PhpPg\PgProto3;

use Amp\ByteStream\ClosedException;
use Amp\ByteStream\ReadableStream;
use Amp\Cancellation;
use Amp\CancelledException;
use InvalidArgumentException;

use function str_repeat;
use function strlen;
use function substr;

class ChunkReader implements ChunkReaderInterface
{
    private string $buffer;
    private int $readPos = 0;
    private int $writePos = 0;

    public function __construct(
        private ReadableStream $stream,
        private int $minBufferSize = 8192,
    ) {
        $this->buffer = $this->newBuffer($this->minBufferSize);
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

        // Buffer size is less than read size
        if (strlen($this->buffer) < $n) {
            $this->copyBuffer($this->newBuffer($n));
        }

        $minReadCount = $n - $availBytesToRead;

        // Buffer size is enough, but need to shift data
        if (strlen($this->buffer) < $minReadCount) {
            $this->copyBuffer($this->newBuffer($minReadCount));
        }

        $this->appendAtLeast($minReadCount, $cancellation);

        $buf = substr($this->buffer, $this->readPos, $n);
        $this->readPos += $n;

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

            $bufLen = strlen($this->buffer);

            // Buffer space is not enough, need to allocate more space
            if ($dataLen > $bufLen - $this->writePos) {
                $this->copyBuffer($this->newBuffer($dataLen + $bufLen));
            }

            for ($i = 0; $i < $dataLen; $i++) {
                $this->buffer[$this->writePos + $i] = $data[$i];
            }

            $this->writePos += $dataLen;
        }
    }

    private function newBuffer(int $len): string
    {
        if ($len < $this->minBufferSize) {
            $len = $this->minBufferSize;
        }

        return str_repeat("\0", $len);
    }

    private function copyBuffer(string $newBuffer): void
    {
        // Copy data
        for ($i = $this->readPos; $i < $this->writePos; $i++) {
            $newBuffer[$i] = $this->buffer[$i];
        }

        $this->writePos = $this->getAvailableBytesToRead();
        $this->readPos = 0;
        $this->buffer = $newBuffer;
    }
}
