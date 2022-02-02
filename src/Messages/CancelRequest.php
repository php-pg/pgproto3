<?php

declare(strict_types=1);

namespace PhpPg\PgProto3\Messages;

use PhpPg\PgProto3\Helper\BinaryStream;

class CancelRequest implements FrontendMessageInterface
{
    use MessageName;

    public const TYPE = '';

    private const CANCEL_REQUEST_CODE = 80877102;

    public int $processId = 0;
    public int $secretKey = 0;

    /**
     * @param int $processId
     * @param int $secretKey
     */
    public function __construct(int $processId, int $secretKey)
    {
        $this->processId = $processId;
        $this->secretKey = $secretKey;
    }

    public function decode(string $data): void
    {
        // TODO: Implement decode() method.
    }

    public function encode(): string
    {
        $buffer = new BinaryStream();
        $buffer->writeUInt32BE(16);
        $buffer->writeUInt32BE(self::CANCEL_REQUEST_CODE);
        $buffer->writeUInt32BE($this->processId);
        $buffer->writeUInt32BE($this->secretKey);

        return $buffer->getBuffer();
    }

    public function getType(): string
    {
        return self::TYPE;
    }
}