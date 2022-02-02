<?php

declare(strict_types=1);

namespace PhpPg\PgProto3\Messages;

use PhpPg\PgProto3\Helper\BinaryStream;

class Parse implements FrontendMessageInterface
{
    use MessageName;

    public const TYPE = 'P';

    /**
     * @param string $name
     * @param string $query
     * @param array<int> $parameterOIDs UINT32
     */
    public function __construct(
        public string $name = '',
        public string $query = '',
        public array $parameterOIDs = [],
    ) {
    }

    public function decode(string $data): void
    {
        // TODO: Implement decode() method.
    }

    public function encode(): string
    {
        $stream = new BinaryStream();
        $stream->writeByte(self::TYPE);
        $stream->writeUInt32BE(0);

        $stream->writeCString($this->name);
        $stream->writeCString($this->query);

        $stream->writeUInt16BE(\count($this->parameterOIDs));
        foreach ($this->parameterOIDs as $parameterOID) {
            $stream->writeUInt32BE($parameterOID);
        }

        $stream->setUInt32BE($stream->getSize() - 1, 1);

        return $stream->getBuffer();
    }

    public function getType(): string
    {
        return self::TYPE;
    }
}