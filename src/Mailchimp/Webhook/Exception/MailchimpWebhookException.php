<?php

declare(strict_types=1);

namespace App\Mailchimp\Webhook\Exception;

class MailchimpWebhookException extends \InvalidArgumentException
{
    public static function missingWebhookType(): self
    {
        return new self('Missing webhook type');
    }

    public static function invalidWebhookType(string $type): self
    {
        return new self('Webhook type is invalid: '.$type);
    }

    public static function missingListId(): self
    {
        return new self('Missing list id');
    }
}
