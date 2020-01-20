<?php

namespace AppBundle\Mandrill;

use AppBundle\Mailer\AbstractEmailTemplate;

final class EmailTemplate extends AbstractEmailTemplate
{
    private $recipientVars = [];

    public function getBody(): array
    {
        if (!\count($this->recipients)) {
            throw new \InvalidArgumentException('The email requires at least one recipient.');
        }

        $body = [
            'template_name' => $this->template,
            'template_content' => [],
            'message' => [
                'subject' => $this->subject,
                'from_email' => $this->senderEmail,
            ],
        ];

        if ($this->vars) {
            $body['message']['global_merge_vars'] = array_map(
                [$this, 'makeMergeFieldStructure'],
                array_keys($this->vars),
                $this->vars
            );
        }

        if ($this->recipientVars) {
            $body['message']['merge_vars'] = $this->recipientVars;
        }

        if ($this->replyTo) {
            $body['message']['headers'] = [
                'Reply-To' => $this->replyTo,
            ];
        }

        if ($this->senderName) {
            $body['message']['from_name'] = $this->senderName;
        }

        foreach ($this->cc as $cc) {
            $this->recipients[] = [
                'email' => $cc,
                'type' => 'cc',
            ];
        }

        foreach ($this->bcc as $bcc) {
            $this->recipients[] = [
                'email' => $bcc,
                'type' => 'bcc',
            ];
        }

        $body['message']['to'] = $this->recipients;

        return $body;
    }

    public function addRecipient(string $email, string $name = null, array $vars = []): void
    {
        $recipient = [
            'email' => $email,
            'type' => 'to',
        ];

        if ($name) {
            $recipient['name'] = $name;
        }

        $this->recipients[] = $recipient;

        if (\count($vars)) {
            $this->recipientVars[] = [
                'rcpt' => $email,
                'vars' => array_map(
                    [$this, 'makeMergeFieldStructure'],
                    array_keys($vars),
                    $vars
                ),
            ];
        }
    }

    public function makeMergeFieldStructure($key, $value): array
    {
        return [
            'name' => $key,
            'content' => $value,
        ];
    }
}
