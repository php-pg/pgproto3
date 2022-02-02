<?php

declare(strict_types=1);

namespace PhpPg\PgProto3\Messages;

use PhpPg\PgProto3\Exception\InvalidMessageFormatException;
use PhpPg\PgProto3\Helper\BinaryStream;

class AuthenticationSASLContinue implements AuthenticationResponseMessage
{
    use MessageName;

    public const AUTH_TYPE = AuthType::AuthTypeSASLContinue;

    public string $data = '';

    public function decode(string $data): void
    {
        if (\strlen($data) < 4) {
            throw new InvalidMessageFormatException($this->getName());
        }

        $stream = new BinaryStream($data);

        $authType = $stream->readUInt32BE();
        if ($authType !== AuthType::AuthTypeSASLContinue->value) {
            throw new InvalidMessageFormatException($this->getName());
        }

        $this->data = $stream->readString($stream->getSize() - $stream->getOffset());
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