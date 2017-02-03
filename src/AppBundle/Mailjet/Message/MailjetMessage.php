<?php

namespace AppBundle\Mailjet\Message;

use Ramsey\Uuid\UuidInterface;

abstract class MailjetMessage
{
    private $uuid;
    private $batch;
    private $vars;
    private $subject;
    private $template;
    private $recipients;

    /**
     * MailjetMessage constructor.
     *
     * @param UuidInterface $uuid           The unique identifier of this message
     * @param string        $template       The Mailjet template ID
     * @param string        $recipientEmail The first recipient email address
     * @param string|null   $recipientName  The first recipient name
     * @param string        $subject        The message subject
     * @param array         $commonVars     The common variables shared by all recipients
     * @param array         $recipientVars  The recipient's specific variables
     * @param UuidInterface $batch
     */
    final public function __construct(
        UuidInterface $uuid,
        string $template,
        string $recipientEmail,
        $recipientName,
        string $subject,
        array $commonVars = [],
        array $recipientVars = [],
        UuidInterface $batch = null
    ) {
        $this->uuid = $uuid;
        $this->recipients = [];
        $this->template = $template;
        $this->subject = $subject;
        $this->vars = $commonVars;
        $this->batch = $batch;

        $this->addRecipient($recipientEmail, $recipientName, $recipientVars);
    }

    /**
     * Sets a common shared variable.
     *
     * @param string $name  The variable name
     * @param string $value The variable value
     */
    protected function setVar(string $name, $value)
    {
        $this->vars[$name] = (string) $value;
    }

    final public function getUuid(): UuidInterface
    {
        return $this->uuid;
    }

    final public function getBatch(): ?UuidInterface
    {
        return $this->batch;
    }

    /**
     * Returns the common variables shared by all recipients.
     *
     * @return array
     */
    final public function getVars(): array
    {
        return $this->vars;
    }

    final public function getSubject(): string
    {
        return $this->subject;
    }

    final public function getTemplate(): string
    {
        return $this->template;
    }

    final public function addRecipient(string $recipientEmail, $recipientName, array $vars = [])
    {
        $key = mb_strtolower($recipientEmail);
        $vars = array_merge($this->vars, $vars);

        $this->recipients[$key] = new MailjetMessageRecipient($recipientEmail, $recipientName, $vars);
    }

    /**
     * Returns the list of MailjetMessageRecipient instances.
     *
     * @return MailjetMessageRecipient[]
     */
    final public function getRecipients(): array
    {
        return array_values($this->recipients);
    }

    final public function getRecipient($key): ?MailjetMessageRecipient
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
}
