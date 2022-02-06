<?php

declare(strict_types=1);

namespace PhpPg\PgProto3;

use Amp\ByteStream\ClosedException;
use Amp\ByteStream\ReadableStream;
use Amp\Cancellation;

class ChunkReader implements ChunkReaderInterface
{
    private string $buffer;
    private int $wp = 0;
    private int $rp = 0;

    public function __construct(
        private ReadableStream $reader,
        private int $minBufferSize = 8192,
    ) {
        $this->buffer = $this->newBuf();
    }

    /**
     * @param Cancellation|null $cancellation
     * @param positive-int|null $n
     * @return string
     * @throws ClosedException
     * @throws \Amp\CancelledException
     */
    public function read(?Cancellation $cancellation = null, ?int $n = null): string
    {
        if ($n === null) {
            throw new \InvalidArgumentException('Number of bytes to read must not be null');
        }

        // n bytes already in buf
        if (($this->wp - $this->rp) >= $n) {
            $buf = \substr($this->buffer, $this->rp, $n);
            $this->rp += $n;

            return $buf;
        }

        if ($this->minBufferSize < $n) {
            throw new \InvalidArgumentException('Cannot read more than buffer size');
        }

        $minReadCount = $n - ($this->wp - $this->rp);

        // buf is large enough, but need to shift filled area to start to make enough contiguous space
        if (($this->minBufferSize - $this->wp) < $minReadCount) {
            $this->shiftBuffer();
        }

        $this->appendAtLeast($minReadCount, $cancellation);

        $result = \substr($this->buffer, $this->rp, $n);
        $this->rp += $n;

        return $result;
    }

    /**
     * @param int $n
     * @param Cancellation|null $cancellation
     * @return void
     * @throws ClosedException
     * @throws \Amp\CancelledException
     */
    private function appendAtLeast(int $n, ?Cancellation $cancellation = null): void
    {
        $readLen = 0;

        while ($readLen < $n) {
            $readBytes = $this->reader->read($cancellation);
            /** @noinspection PhpConditionAlreadyCheckedInspection */
            if ($readBytes === null) {
                throw new ClosedException('Socket closed');
            }

            $readLen += \strlen($readBytes);

            for ($i = 0; $i < $readLen; $i++) {
                $this->buffer[$this->wp + $i] = $readBytes[$i];
            }

            $this->wp += $readLen;
        }
    }

    private function newBuf(): string
    {
        return \str_pad('', $this->minBufferSize, "\0");
    }

    private function shiftBuffer(): void
    {
        $newBuf = $this->newBuf();

        $moveLen = $this->wp - $this->rp;
        $move = \substr($this->buffer, $this->rp, $moveLen);

        for ($i = 0; $i < $moveLen; $i++) {
            $newBuf[$i] = $move[$i];
        }

        $this->wp -= $this->rp;
        $this->rp = 0;
        $this->buffer = $newBuf;
    }

    public function close(): void
    {
        $this->reader->close();
    }

    public function isClosed(): bool
    {
        return $this->reader->isClosed();
    }

    public function isReadable(): bool
    {
        return $this->reader->isReadable();
    }
}
