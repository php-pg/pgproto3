<?php

declare(strict_types=1);

namespace PhpPg\PgProto3\Helper;

use InvalidArgumentException;
use OutOfBoundsException;
use RuntimeException;

use function count;
use function implode;
use function pack;
use function unpack;
use function str_split;

class BinaryStream
{
    /**
     * @var array<int, string>
     */
    private array $buffer;

    private int $offset;

    public function __construct(string $buffer = '', int $offset = 0)
    {
        $this->buffer = self::splitToBytes($buffer);
        $this->setOffset($offset);
    }

    public function writeUInt8(int $val): void
    {
        $byte = pack('C', $val);
        $this->buffer[$this->offset++] = $byte;
    }

    public function setUInt8(int $val, int $offset): void
    {
        $byte = pack('C', $val);
        $this->buffer[$offset] = $byte;
    }

    public function writeUInt16BE(int $val): void
    {
        $short = pack('n', $val);
        foreach (self::splitToBytes($short) as $byte) {
            $this->buffer[$this->offset++] = $byte;
        }
    }

    public function setUInt16BE(int $val, int $offset): void
    {
        $short = pack('n', $val);
        foreach (self::splitToBytes($short) as $byte) {
            $this->buffer[$offset++] = $byte;
        }
    }

    public function writeUInt32BE(int $val): void
    {
        $long = pack('N', $val);
        foreach (self::splitToBytes($long) as $byte) {
            $this->buffer[$this->offset++] = $byte;
        }
    }

    public function setUInt32BE(int $val, int $offset): void
    {
        $long = pack('N', $val);
        foreach (self::splitToBytes($long) as $byte) {
            $this->buffer[$offset++] = $byte;
        }
    }

    public function writeCString(string $str): void
    {
        foreach (self::splitToBytes($str) as $byte) {
            $this->buffer[$this->offset++] = $byte;
        }

        // add NULL terminator
        $this->buffer[$this->offset++] = "\0";
    }

    public function writeString(string $str): void
    {
        foreach (self::splitToBytes($str) as $byte) {
            $this->buffer[$this->offset++] = $byte;
        }
    }

    public function writeByte(string $byte): void
    {
        $this->buffer[$this->offset++] = $byte[0];
    }

    /**
     * @param array<string> $bytes
     * @return void
     */
    public function writeBytes(array $bytes): void
    {
        foreach ($bytes as $byte) {
            $this->buffer[$this->offset++] = $byte;
        }
    }

    /**
     * @return int
     *
     * @throws OutOfBoundsException
     */
    public function readUInt8(): int
    {
        if ($this->getRemainingSize() < 1) {
            throw new OutOfBoundsException('Buffer remaining size is less than 1 byte');
        }

        $byte = unpack('C', $this->buffer[$this->offset++]);
        if (false === $byte) {
            throw new RuntimeException('Cannot read UInt8');
        }

        return $byte[1];
    }

    /**
     * @return int
     *
     * @throws OutOfBoundsException
     */
    public function readUInt16BE(): int
    {
        if ($this->getRemainingSize() < 2) {
            throw new OutOfBoundsException('Buffer remaining size is less than 2 bytes');
        }

        $str = $this->buffer[$this->offset++] . $this->buffer[$this->offset++];
        $short = unpack('n', $str);
        if (false === $short) {
            throw new RuntimeException('Cannot read UInt16');
        }

        return $short[1];
    }

    /**
     * @return int
     *
     * @throws OutOfBoundsException
     */
    public function readUInt32BE(): int
    {
        if ($this->getRemainingSize() < 4) {
            throw new OutOfBoundsException('Buffer remaining size is less than 4 bytes');
        }

        $str = $this->buffer[$this->offset++] .
            $this->buffer[$this->offset++] .
            $this->buffer[$this->offset++] .
            $this->buffer[$this->offset++];

        $long = unpack('N', $str);
        if (false === $long) {
            throw new RuntimeException('Cannot read UInt32');
        }

        return $long[1];
    }

    /**
     * Read C-string (C-string ends with \0 byte in the end)
     *
     * @return string
     * @throws OutOfBoundsException
     */
    public function readCString(): string
    {
        $result = '';
        $found = false;

        while ($this->offset < $this->getSize()) {
            $byte = $this->buffer[$this->offset++];
            if ($byte === "\0") {
                $found = true;
                break;
            }

            $result .= $byte;
        }

        if (!$found) {
            throw new OutOfBoundsException('Cannot find end of CString');
        }

        return $result;
    }

    /**
     * @param int $count
     * @return array<int, string>
     *
     * @throws OutOfBoundsException
     */
    public function readBytes(int $count): array
    {
        if ($this->getRemainingSize() < $count) {
            throw new OutOfBoundsException("Buffer remaining size is less than {$count} bytes");
        }

        $res = [];

        for ($i = 0; $i < $count; $i++) {
            $res[] = $this->buffer[$this->offset++];
        }

        return $res;
    }

    /**
     * @param int $count
     * @return string
     *
     * @throws OutOfBoundsException
     */
    public function readString(int $count): string
    {
        if ($this->getRemainingSize() < $count) {
            throw new OutOfBoundsException("Buffer remaining size is less than {$count} bytes");
        }

        $str = '';

        for ($i = 0; $i < $count; $i++) {
            $str .= $this->buffer[$this->offset++];
        }

        return $str;
    }

    /**
     * @return string
     *
     * @throws OutOfBoundsException
     */
    public function readByte(): string
    {
        if ($this->getRemainingSize() < 1) {
            throw new OutOfBoundsException("Buffer remaining size is less than 1 byte");
        }

        return $this->buffer[$this->offset++];
    }

    public function getByte(int $offset): string
    {
        return $this->buffer[$offset];
    }

    public function getBuffer(): string
    {
        return implode('', $this->buffer);
    }

    public function getOffset(): int
    {
        return $this->offset;
    }

    public function setOffset(int $offset): void
    {
        if ($offset < 0) {
            throw new InvalidArgumentException('Offset cannot be negative');
        }

        if ($offset > $this->getSize()) {
            throw new InvalidArgumentException('Offset cannot be bigger than size');
        }

        $this->offset = $offset;
    }

    /**
     * @return array<int, string>
     */
    public function getRawBuffer(): array
    {
        return $this->buffer;
    }

    public function getSize(): int
    {
        return count($this->buffer);
    }

    public function getRemainingSize(): int
    {
        return $this->getSize() - $this->getOffset();
    }

    public function __toString(): string
    {
        return $this->getBuffer();
    }

    public function eof(): bool
    {
        return $this->getSize() === $this->getOffset();
    }

    /**
     * @param string $str
     * @return array<string> byte array
     */
    private static function splitToBytes(string $str): array
    {
        $result = str_split($str, 1);
        if ($result[0] === '') {
            return [];
        }

        return $result;
    }
}
