<?php

declare(strict_types=1);

namespace PhpPg\PgProto3\Messages;

use PhpPg\PgProto3\Helper\BinaryStream;

class PasswordMessage implements FrontendMessageInterface
{
    use MessageName;

    public const TYPE = 'p';

    public string $password = '';

    public function __construct(string $password)
    {
        $this->password = $password;
    }

    public function decode(string $data): void
    {
        // TODO: Implement decode() method.
    }

    public function encode(): string
    {
        $stream = new BinaryStream();
        $stream->writeByte(self::TYPE);
        $stream->writeUInt32BE(4 + \strlen($this->password) + 1);
        $stream->writeCString($this->password);

        return $stream->getBuffer();
    }

    public function getType(): string
    {
        return self::TYPE;
    }
}