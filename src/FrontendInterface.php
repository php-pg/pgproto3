<?php

declare(strict_types=1);

namespace PhpPg\PgProto3;

use PhpPg\PgProto3\Messages\BackendMessageInterface;
use PhpPg\PgProto3\Messages\FrontendMessageInterface;

interface FrontendInterface
{
    /**
     * @param FrontendMessageInterface $msg
     * @return void
     * @throws \Amp\ByteStream\ClosedException
     */
    public function send(FrontendMessageInterface $msg): void;

    /**
     * @param array<FrontendMessageInterface> $msgs
     * @return void
     * @throws \Amp\ByteStream\ClosedException
     */
    public function sendBulk(array $msgs): void;

    /**
     * @return BackendMessageInterface
     *
     * @throws \Amp\ByteStream\ClosedException
     * @throws \Amp\CancelledException
     * @throws Exception\ProtoException
     */
    public function receive(?\Amp\Cancellation $cancellation = null): BackendMessageInterface;
}
