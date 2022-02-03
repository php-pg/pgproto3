<?php

declare(strict_types=1);

namespace PhpPg\PgProto3\Messages;

interface AuthenticationResponseMessage extends BackendMessageInterface
{
    public const TYPE = 'R';

    public function getAuthType(): AuthType;
}
