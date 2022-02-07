<?php

declare(strict_types=1);

namespace PhpPg\PgProto3;

use Amp\ByteStream\ClosedException;
use Amp\ByteStream\ReadableStream;
use Amp\Cancellation;

interface ChunkReaderInterface extends ReadableStream
{
    /**
     * @param Cancellation|null $cancellation
     * @param int|null $n
     * @return string
     *
     * @throws ClosedException
     */
    public function read(?Cancellation $cancellation = null, ?int $n = null): string;
}
