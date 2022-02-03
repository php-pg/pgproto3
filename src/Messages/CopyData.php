<?php

declare(strict_types=1);

namespace PhpPg\PgProto3\Messages;

use PhpPg\PgProto3\Helper\BinaryStream;

class CopyData implements FrontendMessageInterface, BackendMessageInterface
{
    use MessageName;

    public const TYPE = 'd';

    /**
     * @param string $data Data that forms part of a COPY data stream.
     * Messages sent from the backend will always correspond to single data rows,
     * but messages sent by frontends might divide the data stream arbitrarily.
     */
    public function __construct(
        public string $data = ''
    ) {
    }

    public function decode(string $data): void
    {
        $this->data = $data;
    }

    public function encode(): string
    {
        $stream = new BinaryStream();

        $stream->writeByte(self::TYPE);
        $stream->writeUInt32BE(4 + \strlen($this->data));

        // Small performance hack to directly append data to binary string
        return $stream->getBuffer() . $this->data;
    }

    public function getType(): string
    {
        return self::TYPE;
    }
}
