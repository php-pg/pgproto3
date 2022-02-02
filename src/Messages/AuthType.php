<?php

declare(strict_types=1);

namespace PhpPg\PgProto3\Messages;

enum AuthType: int
{
    case AuthTypeOk = 0;
    case AuthTypeCleartextPassword = 3;
    case AuthTypeMD5Password = 5;
    case AuthTypeSCMCreds = 6;
    case AuthTypeGSS = 7;
    case AuthTypeGSSCont = 8;
    case AuthTypeSSPI = 9;
    case AuthTypeSASL = 10;
    case AuthTypeSASLContinue = 11;
    case AuthTypeSASLFinal = 12;
}