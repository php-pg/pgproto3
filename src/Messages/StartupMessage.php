<?php

declare(strict_types=1);

namespace PhpPg\PgProto3\Messages;

use PhpPg\PgProto3\Helper\BinaryStream;

class StartupMessage implements FrontendMessageInterface
{
    use MessageName;

    public const TYPE = '';

    /** @var int 3.0 */
    public const PROTOCOL_VERSION_NUMBER = 196608;

    public int $protocolVersion = 0;

    /**
     * @var array<string, string>
     */
    public array $parameters = [];

    /**
     * @param int $protocolVersion
     * @param string[] $parameters
     */
    public function __construct(int $protocolVersion, array $parameters)
    {
        $this->protocolVersion = $protocolVersion;
        $this->parameters = $parameters;
    }

    public function decode(string $data): void
    {
        // TODO: Implement decode() method.
    }

    public function encode(): string
    {
        $stream = new BinaryStream();
        $stream->writeUInt32BE(0);

        $stream->writeUInt32BE($this->protocolVersion);
        foreach ($this->parameters as $key => $value) {
            $stream->writeCString($key);
            $stream->writeCString($value);
        }

        $stream->writeByte("\0");

        $stream->setUInt32BE($stream->getSize(), 0);

        return $stream->getBuffer();
    }

    public function getType(): string
    {
        return self::TYPE;
    }
}
