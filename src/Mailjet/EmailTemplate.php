<?php

namespace AppBundle\Mailjet;

use AppBundle\Mailer\EmailTemplate as AbstractEmailTemplate;

final class EmailTemplate extends AbstractEmailTemplate
{
    public function addRecipient(string $email, string $name = null, array $vars = []): void
    {
        $recipient['Email'] = $email;

        if ($name) {
            $recipient['Name'] = $this->fixMailjetParsing($name);
        }

        if (\count($vars)) {
            $recipient['Vars'] = $vars;
        }

        $this->recipients[] = $recipient;
    }

    public function getBody(): array
    {
        if (!\count($this->recipients)) {
            throw new \InvalidArgumentException('The Mailjet email requires at least one recipient.');
        }

        $body['FromEmail'] = $this->senderEmail;

        if ($this->senderName) {
            $body['FromName'] = $this->fixMailjetParsing($this->senderName);
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

        if ($this->bcc) {
            foreach ($this->bcc as $email) {
                $bcc[] = $email;
            }

            $body['Bcc'] = implode(', ', $bcc);
        }

        if ($this->replyTo) {
            $body['Headers'] = [
                'Reply-To' => $this->replyTo,
            ];
        }

        return $body;
    }

    private function fixMailjetParsing(?string $string): ?string
    {
        return str_replace(',', '', $string);
    }
}
