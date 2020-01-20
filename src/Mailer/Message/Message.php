<?php

namespace AppBundle\Mailer\Message;

use Ramsey\Uuid\UuidInterface;

class Message
{
    private const MESSAGE_CLASS_SUFFIX = 'Message';

    protected $uuid;
    protected $vars;
    protected $subject;
    protected $template;
    protected $recipients;
    protected $replyTo;
    protected $senderEmail;
    protected $senderName;
    protected $cc;
    protected $bcc;

    /**
     * Message constructor.
     *
     * @param UuidInterface $uuid           The unique identifier of this message
     * @param string        $template       The Message template ID
     * @param string        $recipientEmail The first recipient email address
     * @param string|null   $recipientName  The first recipient name
     * @param string        $subject        The message subject
     * @param array         $commonVars     The common variables shared by all recipients
     * @param array         $recipientVars  The recipient's specific variables
     * @param string        $replyTo        The email address to use for the Reply-to header
     */
    final public function __construct(
        UuidInterface $uuid,
        string $recipientEmail,
        $recipientName,
        string $subject,
        array $commonVars = [],
        array $recipientVars = [],
        string $replyTo = null,
        string $template = null
    ) {
        $this->uuid = $uuid;
        $this->recipients = [];
        $this->subject = $subject;
        $this->vars = $commonVars;
        $this->replyTo = $replyTo;
        $this->cc = [];
        $this->bcc = [];
        $this->template = $template;

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

    final public function getSubject(): string
    {
        return $this->subject;
    }

    final public function getTemplate(): ?string
    {
        return $this->template;
    }

    public function getReplyTo(): ?string
    {
        return $this->replyTo;
    }

    final public function addRecipient(string $recipientEmail, $recipientName = null, array $vars = []): void
    {
        $this->recipients[mb_strtolower($recipientEmail)] = new MessageRecipient($recipientEmail, $recipientName, $vars);
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
        if (!\is_int($key) && !\is_string($key)) {
            throw new \InvalidArgumentException('Recipient key must be an integer index or valid email address string.');
        }

        if (\is_string($key) && \array_key_exists($key = mb_strtolower($key), $this->recipients)) {
            return $this->recipients[$key];
        }

        $recipients = $this->getRecipients();

        return $recipients[$key] ?? null;
    }

    final protected static function escape(string $string): string
    {
        return htmlspecialchars($string, \ENT_NOQUOTES, 'UTF-8', false);
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

    public function getBCC(): array
    {
        return $this->bcc;
    }

    public function addBCC(string $bcc): void
    {
        $this->bcc[] = $bcc;
    }

    public function setReplyTo(string $replyTo): void
    {
        $this->replyTo = $replyTo;
    }

    /**
     * Transforms the mail class name from `CamelCase` to `snake_case` and remove Message word from the end
     */
    public function generateTemplateName(): string
    {
        $parts = explode('\\', static::class);

        return strtolower(
            preg_replace(
                sprintf('#-%s$#', self::MESSAGE_CLASS_SUFFIX), // Remove _Message from the end
                '',
                ltrim(preg_replace('#[A-Z]([A-Z](?![a-z]))*#', '-$0', end($parts)), '-')
            )
        );
    }

    protected static function formatDate(\DateTimeInterface $date, string $format): string
    {
        return (new \IntlDateFormatter(
            'fr_FR',
            \IntlDateFormatter::NONE,
            \IntlDateFormatter::NONE,
            $date->getTimezone(),
            \IntlDateFormatter::GREGORIAN,
            $format
        ))->format($date);
    }
}
