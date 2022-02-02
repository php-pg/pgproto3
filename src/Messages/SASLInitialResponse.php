<?php

declare(strict_types=1);

namespace PhpPg\PgProto3\Messages;

use PhpPg\PgProto3\Helper\BinaryStream;

class SASLInitialResponse implements FrontendMessageInterface
{
    use MessageName;

    public const TYPE = 'p';

    public string $authMechanism = '';

    public string $data = '';

    /**
     * @param string $authMechanism
     * @param string $data
     */
    public function __construct(string $authMechanism, string $data)
    {
        $this->authMechanism = $authMechanism;
        $this->data = $data;
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

        $stream->writeCString($this->authMechanism);
        $stream->writeUInt32BE(\strlen($this->data));
        $stream->writeString($this->data);

        $stream->setUInt32BE($stream->getSize() - 1, 1);

        return $stream->getBuffer();
    }

    public function getType(): string
    {
        return self::TYPE;
    }
}