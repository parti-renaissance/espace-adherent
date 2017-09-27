<?php

namespace AppBundle\Mailjet;

use AppBundle\Mailer\EmailTemplate as AbstractEmailTemplate;
use AppBundle\Mailjet\Exception\MailjetException;
use Ramsey\Uuid\UuidInterface;

final class EmailTemplate extends AbstractEmailTemplate
{
    public function __construct(
        UuidInterface $uuid,
        string $template,
        string $subject,
        string $senderEmail,
        string $senderName = null,
        string $replyTo = null,
        array $cc = []
    ) {
        $senderName = $this->fixMailjetParsing($senderName);

        parent::__construct(
            $uuid,
            $template,
            $subject,
            $senderEmail,
            $senderName,
            $replyTo,
            $cc
        );
    }

    public function addRecipient(string $email, string $name = null, array $vars = []): void
    {
        $recipient['Email'] = $email;

        if ($name) {
            $recipient['Name'] = $this->fixMailjetParsing($name);
        }

        if (count($vars)) {
            $recipient['Vars'] = $vars;
        }

        $this->recipients[] = $recipient;
    }

    public function getBody(): array
    {
        if (!count($this->recipients)) {
            throw new MailjetException('The Mailjet email requires at least one recipient.');
        }

        $body['FromEmail'] = $this->senderEmail;
        if ($this->senderName) {
            $body['FromName'] = $this->senderName;
        }

        $body['Subject'] = $this->subject;
        $body['MJ-TemplateID'] = $this->template;
        $body['MJ-TemplateLanguage'] = true;

        if ($this->cc) {
            $to = [];

            if ($this->recipients) {
                $body['Vars'] = $this->recipients[0]['Vars'];

                foreach ($this->recipients as $recipient) {
                    if (isset($recipient['Name'])) {
                        $to[] = sprintf('"%s" <%s>', $recipient['Name'], $recipient['Email']);
                    } else {
                        $to[] = $recipient['Email'];
                    }
                }
            }

            foreach ($this->cc as $cc) {
                $to[] = $cc;
            }

            $body['To'] = implode(', ', $to);
        } else {
            $body['Recipients'] = $this->recipients;
        }

        if ($this->replyTo) {
            $body['Headers'] = [
                'Reply-To' => $this->replyTo,
            ];
        }

        return $body;
    }

    private function fixMailjetParsing(string $string = null): ?string
    {
        return str_replace(',', '', $string);
    }
}
