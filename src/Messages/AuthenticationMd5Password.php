<?php

declare(strict_types=1);

namespace PhpPg\PgProto3\Messages;

use PhpPg\PgProto3\Exception\InvalidMessageFormatException;
use PhpPg\PgProto3\Exception\InvalidMessageSizeException;
use PhpPg\PgProto3\Helper\BinaryStream;

class AuthenticationMd5Password implements AuthenticationResponseMessage
{
    use MessageName;

    public const AUTH_TYPE = AuthType::AuthTypeMD5Password;

    public string $salt = '';

    public function decode(string $data): void
    {
        if (\strlen($data) !== 8) {
            throw new InvalidMessageSizeException($this->getName(), 8, \strlen($data));
        }

        $stream = new BinaryStream($data);

        $authType = $stream->readUInt32BE();
        if ($authType !== self::AUTH_TYPE->value) {
            throw new InvalidMessageFormatException($this->getName());
        }

        $this->salt = $stream->readString(4);
    }

    public function encode(): string
    {
        // TODO: Implement encode() method.
        return '';
    }

    public function getType(): string
    {
        return self::TYPE;
    }

    public function getAuthType(): AuthType
    {
        return self::AUTH_TYPE;
    }
}