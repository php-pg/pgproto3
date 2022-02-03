<?php

declare(strict_types=1);

namespace PhpPg\PgProto3\Messages;

use PhpPg\PgProto3\Exception\InvalidMessageSizeException;
use PhpPg\PgProto3\Exception\InvalidMessageFormatException;

interface MessageInterface
{
    /**
     * @param string $data
     * @return void
     *
     * @throws InvalidMessageSizeException
     * @throws InvalidMessageFormatException
     */
    public function decode(string $data): void;

    public function encode(): string;

    /**
     * Get protocol message name
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Get message type (PostgreSQL name), 1byte string
     *
     * @return string
     */
    public function getType(): string;
}
