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
        $body['Recipients'] = $this->recipients;

        /**
         * CC :
         *  - We don't use the recipients option
         *  - We put the recipients and CC emails into the To option
         */
        if ($this->cc) {
            unset($body['Recipients']);

            $to = [];

            if ($this->recipients) {
                $body['Vars'] = $this->recipients[0]['Vars'];
                $to = $this->createToByRecipients();
            }

            foreach ($this->cc as $cc) {
                $to[] = $cc;
            }

            $body['To'] = implode(', ', $to);
        }

        /**
         * BCC :
         * - We don't use the recipients option
         * - We put the recipients and the CC emails if the CC option is used, into the To option
         * - We put the BCC emails into the Bcc option
         */
        if ($this->bcc) {
            unset($body['Recipients']);

            if ($this->recipients) {
                $body['Vars'] = $this->recipients[0]['Vars'];

                if (!isset($body['To'])) {
                    $body['To'] = implode(', ', $this->createToByRecipients());
                }
            }

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

    private function createToByRecipients(): array
    {
        $to = [];

        if ($this->recipients) {
            foreach ($this->recipients as $recipient) {
                if (isset($recipient['Name'])) {
                    $to[] = sprintf('"%s" <%s>', $recipient['Name'], $recipient['Email']);
                } else {
                    $to[] = $recipient['Email'];
                }
            }
        }

        return $to;
    }

    private function fixMailjetParsing(?string $string): ?string
    {
        return str_replace(',', '', $string);
    }
}
