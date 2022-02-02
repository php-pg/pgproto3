<?php

declare(strict_types=1);

namespace PhpPg\PgProto3\Exception;

class UnknownMessageTypeException extends ProtoException
{
    private string $msgType;

    public function __construct(string $msgType)
    {
        parent::__construct(\sprintf("Unknown message type received: %c", $msgType));

        $this->msgType = $msgType;
    }

    public function getMsgType(): string
    {
        return $this->msgType;
    }
}