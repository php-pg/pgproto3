<?php

declare(strict_types=1);

namespace PhpPg\PgProto3\Tests;

use Amp\ByteStream\ClosedException;
use Amp\ByteStream\ReadableStream;
use Amp\ByteStream\WritableStream;
use Amp\Cancellation;
use PhpPg\PgProto3\ChunkReader;
use PhpPg\PgProto3\Frontend;
use PhpPg\PgProto3\Messages\ErrorResponse;
use PhpPg\PgProto3\Messages\ReadyForQuery;
use PHPUnit\Framework\TestCase;

class FrontendTest extends TestCase
{
    private ReadableStream $reader;
    private WritableStream $writer;
    private Frontend $frontend;

    protected function setUp(): void
    {
        parent::setUp();

        $this->reader = new class implements ReadableStream {
            private array $chunks = [];

            public function push(string $data): void
            {
                $this->chunks[] = $data;
            }

            public function read(?Cancellation $cancellation = null): string
            {
                $data = \array_shift($this->chunks);
                if ($data === null) {
                    throw new ClosedException('Reader closed');
                }

                return $data;
            }

            public function close(): void
            {
            }

            public function isClosed(): bool
            {
                return false;
            }

            public function isReadable(): bool
            {
                return true;
            }
        };

        $this->writer = $this->createMock(WritableStream::class);
        $this->frontend = new Frontend(new ChunkReader($this->reader), $this->writer);
    }

    public function testFrontendReceiveInterrupted(): void
    {
        $this->reader->push("Z\0\0\0\5");

        try {
            $msg = $this->frontend->receive();
        } catch (ClosedException) {

        }
        if (isset($msg)) {
            $this->fail('Exception expected');
        }

        $this->reader->push("I");

        $msg = $this->frontend->receive();
        self::assertInstanceOf(ReadyForQuery::class, $msg);
        self::assertSame('I', $msg->txStatus);
    }

    public function testErrorResponse(): void
    {
        $want = new ErrorResponse(
            severity: 'ERROR',
            severityNonLocalized: 'ERROR',
            code: '42703',
            message: 'Column "foo" does not exist',
            position: 8,
            file: 'parse_relation.c',
            line: 3513,
            routine: 'errorMissingColumn',
        );

        $raw = [
            'E', "\0", "\0", "\0", 'f',
            'S', 'E', 'R', 'R', 'O', 'R', "\0",
            'V', 'E', 'R', 'R', 'O', 'R', "\0",
            'C', '4', '2', '7', '0', '3', "\0",
            'M', 'C', 'o', 'l', 'u', 'm', 'n', "\x20",
            '"', 'f', 'o', 'o', '"', "\x20",
            'd', 'o', 'e', 's', "\x20",
            'n', 'o', 't', "\x20",
            'e', 'x', 'i', 's', 't', "\0",
            'P', '8', "\0",
            'F', 'p', 'a', 'r', 's', 'e', '_', 'r', 'e', 'l', 'a', 't', 'i', 'o', 'n', '.', 'c', "\0",
            'L', '3', '5', '1', '3', "\0",
            'R', 'e', 'r', 'r', 'o', 'r', 'M', 'i', 's', 's', 'i', 'n', 'g', 'C', 'o', 'l', 'u', 'm', 'n', "\0", "\0",
        ];

        $this->reader->push(\implode('', $raw));

        $msg = $this->frontend->receive();
        self::assertSame(\json_encode($want, \JSON_THROW_ON_ERROR), \json_encode($msg, \JSON_THROW_ON_ERROR));
    }
}