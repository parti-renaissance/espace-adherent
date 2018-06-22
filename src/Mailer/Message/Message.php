<?php

namespace AppBundle\Mailer\Message;

use Ramsey\Uuid\UuidInterface;

/**
 * @todo set the class abstract once all projects are migrated; e.g. MailerConsumer is not receiving messages anymore
 */
class Message
{
    protected $uuid;
    protected $vars;
    protected $recipients;
    protected $replyTo;
    protected $senderEmail;
    protected $senderName;
    protected $cc;
    protected $subject;
    protected $app;
    protected $messageClass;

    /**
     * Only needed to manage legacy message format
     * If set it means it's a legacy message
     *
     * @todo delete me once all projects are migrated
     */
    protected $template;

    /**
     * Message constructor.
     *
     * @param UuidInterface $uuid           The unique identifier of this message
     * @param string        $recipientEmail The first recipient email address
     * @param string|null   $recipientName  The first recipient name
     * @param array         $commonVars     The common variables shared by all recipients
     * @param array         $recipientVars  The recipient's specific variables
     * @param string        $replyTo        The email address to use for the Reply-to header
     */
    final public function __construct(
        UuidInterface $uuid,
        string $recipientEmail,
        ?string $recipientName,
        array $commonVars = [],
        array $recipientVars = [],
        string $replyTo = null
    ) {
        $this->uuid = $uuid;
        $this->recipients = [];
        $this->vars = $commonVars;
        $this->replyTo = $replyTo;
        $this->cc = [];
        $this->app = 'EM';

        $this->addRecipient($recipientEmail, $recipientName, $recipientVars);
    }

    final public function getUuid(): UuidInterface
    {
        return $this->uuid;
    }

    /**
     * Returns the common variables shared by all recipients.
     */
    final public function getVars(): array
    {
        return $this->vars;
    }

    public function getReplyTo(): ?string
    {
        return $this->replyTo;
    }

    final public function addRecipient(string $recipientEmail, $recipientName = null, array $vars = []): void
    {
        $key = mb_strtolower($recipientEmail);
        $vars = array_merge($this->vars, $vars);

        $this->recipients[$key] = new MessageRecipient($recipientEmail, $recipientName, $vars);
    }

    /**
     * Returns the list of MessageRecipient instances.
     *
     * @return MessageRecipient[]
     */
    final public function getRecipients(): array
    {
        return array_values($this->recipients);
    }

    final public function getRecipient($key): ?MessageRecipient
    {
        if (!is_int($key) && !is_string($key)) {
            throw new \InvalidArgumentException('Recipient key must be an integer index or valid email address string.');
        }

        if (is_string($key) && array_key_exists($key = mb_strtolower($key), $this->recipients)) {
            return $this->recipients[$key];
        }

        $recipients = $this->getRecipients();

        return $recipients[$key] ?? null;
    }

    final protected static function escape(string $string): string
    {
        return htmlspecialchars($string, ENT_NOQUOTES, 'UTF-8', false);
    }

    final protected static function urlEncode(string $string): string
    {
        return urlencode($string);
    }

    public function getSenderEmail(): ?string
    {
        return $this->senderEmail;
    }

    public function setSenderEmail(?string $senderEmail): void
    {
        $this->senderEmail = $senderEmail;
    }

    public function getSenderName(): ?string
    {
        return $this->senderName;
    }

    public function setSenderName(?string $senderName): void
    {
        $this->senderName = $senderName;
    }

    public function getCC(): array
    {
        return $this->cc;
    }

    public function addCC(string $cc): void
    {
        $this->cc[] = $cc;
    }

    public function setReplyTo(string $replyTo): void
    {
        $this->replyTo = $replyTo;
    }

    public function setSubject(string $subject): void
    {
        $this->subject = $subject;
    }

    public function getSubject(): ?string
    {
        return $this->subject;
    }

    /**
     * Only needed to manage legacy message format
     *
     * @todo delete me once all projects are migrated
     */
    public function setTemplate(string $template): void
    {
        // This class is meant to be abstract once the legacy code is removed -> e.g.: MailerConsumer
        // This is a safety chek to make sure we are not using v1 message while they are v2
        if (__CLASS__ !== get_class($this)) {
            throw new \LogicException(sprintf('External app can only be set with %s instance', __CLASS__));
        }

        $this->template = $template;
    }

    /**
     * Only needed to manage legacy message format
     *
     * @todo delete me once all projects are migrated
     */
    public function getTemplate(): ?string
    {
        return $this->template;
    }

    /**
     * Only needed to manage legacy message format
     *
     * @todo delete me once all projects are migrated
     */
    public function isV2(): bool
    {
        return null === $this->getTemplate();
    }

    public function setApp(string $app): void
    {
        $this->app = $app;
    }

    public function getApp(): string
    {
        return $this->app;
    }
}
