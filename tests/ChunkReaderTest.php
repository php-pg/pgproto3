<?php

declare(strict_types=1);

namespace PhpPg\PgProto3\Tests;

use Amp\ByteStream\ReadableStream;
use PhpPg\PgProto3\ChunkReader;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ChunkReaderTest extends TestCase
{
    private ReadableStream|MockObject $stream;
    private ChunkReader $reader;

    protected function setUp(): void
    {
        parent::setUp();

        $this->stream = $this->createMock(ReadableStream::class);
        $this->reader = new ChunkReader($this->stream, 100);
    }

    public function testReadMoreThanBufferSize(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot read more than buffer size');

        $this->reader->read(null, 101);
    }

    public function testReturnsDataWithoutIO(): void
    {
        $this
            ->stream
            ->expects(self::once())
            ->method('read')
            ->willReturn(\str_pad('', 100, "\0"));

        $this->reader->read(null, 50);
        $this->reader->read(null, 50);
    }

    public function testDoingIOWhenNeedMoreData(): void
    {
        $this
            ->stream
            ->expects(self::exactly(2))
            ->method('read')
            ->willReturnOnConsecutiveCalls(
                \str_pad('', 100, "a"),
                "b"
            );

        $this->reader->read(null, 50);
        $data = $this->reader->read(null, 51);

        self::assertSame("b", $data[50]);
    }
}