<?php

namespace AppBundle\Mailchimp\Webhook\Handler;

interface WebhookHandlerInterface
{
    public function handle(array $data): void;

    public function support(string $type): bool;
}
