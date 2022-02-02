<?php

declare(strict_types=1);

namespace PhpPg\PgProto3;

use Amp\ByteStream\ClosedException;
use Amp\ByteStream\WritableStream;
use Amp\Cancellation;
use PhpPg\PgProto3\Helper\BinaryStream;
use PhpPg\PgProto3\Messages\AuthenticationCleartextPassword;
use PhpPg\PgProto3\Messages\AuthenticationMd5Password;
use PhpPg\PgProto3\Messages\AuthenticationOk;
use PhpPg\PgProto3\Messages\AuthenticationResponseMessage;
use PhpPg\PgProto3\Messages\AuthenticationSASL;
use PhpPg\PgProto3\Messages\AuthenticationSASLContinue;
use PhpPg\PgProto3\Messages\AuthenticationSASLFinal;
use PhpPg\PgProto3\Messages\AuthType;
use PhpPg\PgProto3\Messages\BackendKeyData;
use PhpPg\PgProto3\Messages\BackendMessageInterface;
use PhpPg\PgProto3\Messages\BindComplete;
use PhpPg\PgProto3\Messages\CloseComplete;
use PhpPg\PgProto3\Messages\CommandComplete;
use PhpPg\PgProto3\Messages\CopyBothResponse;
use PhpPg\PgProto3\Messages\CopyData;
use PhpPg\PgProto3\Messages\CopyDone;
use PhpPg\PgProto3\Messages\CopyInResponse;
use PhpPg\PgProto3\Messages\CopyOutResponse;
use PhpPg\PgProto3\Messages\DataRow;
use PhpPg\PgProto3\Messages\EmptyQueryResponse;
use PhpPg\PgProto3\Messages\ErrorResponse;
use PhpPg\PgProto3\Messages\FrontendMessageInterface;
use PhpPg\PgProto3\Messages\FunctionCallResponse;
use PhpPg\PgProto3\Messages\MessageInterface;
use PhpPg\PgProto3\Messages\NoData;
use PhpPg\PgProto3\Messages\NoticeResponse;
use PhpPg\PgProto3\Messages\NotificationResponse;
use PhpPg\PgProto3\Messages\ParameterDescription;
use PhpPg\PgProto3\Messages\ParameterStatus;
use PhpPg\PgProto3\Messages\ParseComplete;
use PhpPg\PgProto3\Messages\PortalSuspended;
use PhpPg\PgProto3\Messages\ReadyForQuery;
use PhpPg\PgProto3\Messages\RowDescription;
use Psr\Log\LoggerInterface;

class Frontend implements FrontendInterface
{
    private bool $partialMsg = false;
    private string $msgType = '';
    private int $msgBodyLen = 0;

    private AuthenticationOk $authenticationOk;
    private AuthenticationCleartextPassword $authenticationCleartextPassword;
    private AuthenticationMd5Password $authenticationMd5Password;
    private AuthenticationSASL $authenticationSASL;
    private AuthenticationSASLContinue $authenticationSASLContinue;
    private AuthenticationSASLFinal $authenticationSASLFinal;

    private BackendKeyData $backendKeyData;
    private ParameterStatus $parameterStatus;
    private ReadyForQuery $readyForQuery;
    private ErrorResponse $errorResponse;
    private NoticeResponse $noticeResponse;
    private NotificationResponse $notificationResponse;

    private ParseComplete $parseComplete;
    private BindComplete $bindComplete;
    private CloseComplete $closeComplete;
    private EmptyQueryResponse $emptyQueryResponse;
    private NoData $noData;
    private CommandComplete $commandComplete;
    private RowDescription $rowDescription;
    private ParameterDescription $parameterDescription;
    private DataRow $dataRow;
    private PortalSuspended $portalSuspended;

    private FunctionCallResponse $functionCallResponse;

    private CopyData $copyData;
    private CopyInResponse $copyInResponse;
    private CopyOutResponse $copyOutResponse;
    private CopyBothResponse $copyBothResponse;
    private CopyDone $copyDone;

    public function __construct(
        private ChunkReaderInterface $reader,
        private WritableStream $writer,
        private ?LoggerInterface $logger = null
    ) {
        $this->authenticationOk = new AuthenticationOk();
        $this->authenticationCleartextPassword = new AuthenticationCleartextPassword();
        $this->authenticationMd5Password = new AuthenticationMd5Password();
        $this->authenticationSASL = new AuthenticationSASL();
        $this->authenticationSASLContinue = new AuthenticationSASLContinue();
        $this->authenticationSASLFinal = new AuthenticationSASLFinal();

        $this->backendKeyData = new BackendKeyData();
        $this->parameterStatus = new ParameterStatus();
        $this->readyForQuery = new ReadyForQuery();
        $this->errorResponse = new ErrorResponse();
        $this->noticeResponse = new NoticeResponse();
        $this->notificationResponse = new NotificationResponse();

        $this->parseComplete = new ParseComplete();
        $this->bindComplete = new BindComplete();
        $this->closeComplete = new CloseComplete();
        $this->emptyQueryResponse = new EmptyQueryResponse();
        $this->noData = new NoData();
        $this->commandComplete = new CommandComplete();
        $this->rowDescription = new RowDescription();
        $this->parameterDescription = new ParameterDescription();
        $this->dataRow = new DataRow();
        $this->portalSuspended = new PortalSuspended();

        $this->functionCallResponse = new FunctionCallResponse();

        $this->copyData = new CopyData();
        $this->copyInResponse = new CopyInResponse();
        $this->copyOutResponse = new CopyOutResponse();
        $this->copyBothResponse = new CopyBothResponse();
        $this->copyDone = new CopyDone();
    }

    /**
     * @param FrontendMessageInterface $msg
     * @return void
     * @throws ClosedException
     */
    public function send(FrontendMessageInterface $msg): void
    {
        $this->logger?->debug(
            __METHOD__ . ' sending message',
            ['type' => $msg->getType(), 'name' => $msg->getName(), 'data' => $this->dumpMessage($msg)]
        );

        $encode = $msg->encode();
        $this->writer->write($encode);

        $this->logger?->debug(
            __METHOD__ . ' message sent',
            ['type' => $msg->getType(), 'name' => $msg->getName()]
        );
    }

    /**
     * @param array<FrontendMessageInterface> $msgs
     * @return void
     * @throws ClosedException
     */
    public function sendBulk(array $msgs): void
    {
        $buffer = '';

        foreach ($msgs as $msg) {
            $buffer .= $msg->encode();

            $this->logger?->debug(
                __METHOD__ . ' sending message (in bulk)',
                ['type' => $msg->getType(), 'name' => $msg->getName(), 'data' => $this->dumpMessage($msg)]
            );
        }

        $this->writer->write($buffer);

        foreach ($msgs as $msg) {
            $this->logger?->debug(
                __METHOD__ . ' message sent (in bulk)',
                ['type' => $msg->getType(), 'name' => $msg->getName()]
            );
        }
    }

    /**
     * @return BackendMessageInterface
     * @throws ClosedException
     */
    public function receive(?Cancellation $cancellation = null): BackendMessageInterface
    {
        if (!$this->partialMsg) {
            // read message header (1 byte message type, 4 byte message body size, including size field)
            $msgHeader = $this->reader->read($cancellation, 5);
            $buffer = new BinaryStream($msgHeader);

            $this->msgType = $buffer->readByte();
            $this->msgBodyLen = $buffer->readUInt32BE() - 4;
            $this->partialMsg = true;

            $this->logger?->debug(
                __METHOD__ . ' received message',
                ['type' => $this->msgType, 'len' => $this->msgBodyLen],
            );
        }

        $msgBody = '';
        if ($this->msgBodyLen > 0) {
            $msgBody = $this->reader->read($cancellation, $this->msgBodyLen);
        }

        $this->partialMsg = false;

        $msg = match ($this->msgType) {
            ParseComplete::TYPE => $this->parseComplete,
            BindComplete::TYPE => $this->bindComplete,
            CloseComplete::TYPE => $this->closeComplete,
            NotificationResponse::TYPE => $this->notificationResponse,
            CopyDone::TYPE => $this->copyDone,
            CommandComplete::TYPE => $this->commandComplete,
            CopyData::TYPE => $this->copyData,
            DataRow::TYPE => $this->dataRow,
            ErrorResponse::TYPE => $this->errorResponse,
            CopyInResponse::TYPE => $this->copyInResponse,
            CopyOutResponse::TYPE => $this->copyOutResponse,
            EmptyQueryResponse::TYPE => $this->emptyQueryResponse,
            BackendKeyData::TYPE => $this->backendKeyData,
            NoData::TYPE => $this->noData,
            NoticeResponse::TYPE => $this->noticeResponse,
            AuthenticationResponseMessage::TYPE => $this->findAuthMessageType($msgBody),
            PortalSuspended::TYPE => $this->portalSuspended,
            ParameterStatus::TYPE => $this->parameterStatus,
            ParameterDescription::TYPE => $this->parameterDescription,
            RowDescription::TYPE => $this->rowDescription,
            FunctionCallResponse::TYPE => $this->functionCallResponse,
            CopyBothResponse::TYPE => $this->copyBothResponse,
            ReadyForQuery::TYPE => $this->readyForQuery,
            default => throw new Exception\UnknownMessageTypeException($this->msgType),
        };

        $msg->decode($msgBody);

        $this->logger?->debug(
            __METHOD__ . ' decoded message',
            ['type' => $this->msgType, 'name' => $msg->getName(), 'data' => $this->dumpMessage($msg)]
        );

        return $msg;
    }

    /**
     * @param string $body
     * @return AuthenticationResponseMessage
     * @throws Exception\UnknownAuthMessageTypeException
     * @throws Exception\InvalidMessageFormatException
     */
    private function findAuthMessageType(string $body): AuthenticationResponseMessage
    {
        if (\strlen($body) < 4) {
            throw new Exception\InvalidMessageFormatException('Authentication*', null);
        }

        $authType = (new BinaryStream($body))->readUInt32BE();

        return match ($authType) {
            AuthType::AuthTypeOk->value => $this->authenticationOk,
            AuthType::AuthTypeCleartextPassword->value => $this->authenticationCleartextPassword,
            AuthType::AuthTypeMD5Password->value => $this->authenticationMd5Password,
            AuthType::AuthTypeSASL->value => $this->authenticationSASL,
            AuthType::AuthTypeSASLContinue->value => $this->authenticationSASLContinue,
            AuthType::AuthTypeSASLFinal->value => $this->authenticationSASLFinal,
            default => throw new Exception\UnknownAuthMessageTypeException($authType),
        };
    }

    /**
     * @param MessageInterface $msg
     * @return array<string, mixed>
     */
    private function dumpMessage(MessageInterface $msg): array
    {
        return \get_object_vars($msg);
    }
}