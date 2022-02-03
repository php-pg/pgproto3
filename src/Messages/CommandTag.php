<?php

declare(strict_types=1);

namespace PhpPg\PgProto3\Messages;

class CommandTag implements \Stringable
{
    public function __construct(
        public string $tag
    ) {
    }

    public function rowsAffected(): int
    {
        $spaceChar = \strpos($this->tag, ' ');
        if (false === $spaceChar) {
            return 0;
        }

        return (int)\substr($this->tag, $spaceChar);
    }

    public function __toString(): string
    {
        return $this->tag;
    }
}
