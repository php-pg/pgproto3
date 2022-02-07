<?php

declare(strict_types=1);

namespace PhpPg\PgProto3\Tests;

use Amp\ByteStream\ReadableStream;
use PhpPg\PgProto3\ChunkReader;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

use function str_repeat;
use function strlen;
use function substr;

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
        $this
            ->stream
            ->expects(self::exactly(2))
            ->method('read')
            ->willReturnOnConsecutiveCalls(
                str_repeat("0", 100),
                "1",
            );

        $result = $this->reader->read(null, 101);
        self::assertSame(strlen($result), 101);
        self::assertSame(str_repeat('0', 100) . '1', $result);
    }

    public function testReadStreamReturnedMoreThanBufferSize(): void
    {
        $str = '';

        for ($i = 0; $i < 30; $i++) {
            for ($j = 0; $j < 10; $j++) {
                $str .= $j;
            }
        }

        $this
            ->stream
            ->expects(self::exactly(2))
            ->method('read')
            ->willReturnOnConsecutiveCalls(
                substr($str, 0, 100),
                // Stream returned more data than requested and more than current buffer size
                '1' . substr($str, 100, 200),
            );

        $result = $this->reader->read(null, 101);
        self::assertSame(strlen($result), 101);
        self::assertSame(substr($str, 0, 100) . '1', $result);

        $result = $this->reader->read(null, 150);
        self::assertSame(150, strlen($result));
        self::assertSame(substr($str, 100, 150), $result);

        $result = $this->reader->read(null, 50);
        self::assertSame(50, strlen($result));
        self::assertSame(substr($str, 250, 50), $result);
    }

    public function testReturnsDataWithoutIO(): void
    {
        $this
            ->stream
            ->expects(self::once())
            ->method('read')
            ->willReturn(str_repeat('0', 50) . str_repeat('1', 50));

        $result = $this->reader->read(null, 50);
        self::assertSame(50, strlen($result));
        self::assertSame(str_repeat('0', 50), $result);

        $result = $this->reader->read(null, 50);
        self::assertSame(50, strlen($result));
        self::assertSame(str_repeat('1', 50), $result);
    }

    public function testDoingIOWhenNeedMoreData(): void
    {
        $this
            ->stream
            ->expects(self::exactly(2))
            ->method('read')
            ->willReturnOnConsecutiveCalls(
                str_repeat('a', 100),
                "b"
            );

        $this->reader->read(null, 50);
        $data = $this->reader->read(null, 51);

        self::assertSame("b", $data[50]);
    }
}