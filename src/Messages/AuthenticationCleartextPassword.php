<?php

declare(strict_types=1);

namespace PhpPg\PgProto3\Messages;

use PhpPg\PgProto3\Exception\InvalidMessageFormatException;
use PhpPg\PgProto3\Exception\InvalidMessageSizeException;
use PhpPg\PgProto3\Helper\BinaryStream;

class AuthenticationCleartextPassword implements AuthenticationResponseMessage
{
    use MessageName;

    public const AUTH_TYPE = AuthType::AuthTypeCleartextPassword;

    public function decode(string $data): void
    {
        if (\strlen($data) !== 4) {
            throw new InvalidMessageSizeException($this->getName(), 4, \strlen($data));
        }

        $stream = new BinaryStream($data);

        $authType = $stream->readUInt32BE();
        if ($authType !== AuthType::AuthTypeCleartextPassword->value) {
            throw new InvalidMessageFormatException($this->getName());
        }
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
