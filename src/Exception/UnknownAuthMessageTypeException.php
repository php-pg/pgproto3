<?php

declare(strict_types=1);

namespace PhpPg\PgProto3\Exception;

class UnknownAuthMessageTypeException extends ProtoException
{
    private int $msgType;

    public function __construct(int $msgType)
    {
        parent::__construct(\sprintf("Unknown authentication message type received: %d", $msgType));

        $this->msgType = $msgType;
    }

    public function getMsgType(): int
    {
        return $this->msgType;
    }
}
