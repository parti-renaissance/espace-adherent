<?php

namespace AppBundle\Mailchimp\Webhook;

use AppBundle\Mailchimp\Webhook\Handler\WebhookHandlerInterface;

class WebhookHandler
{
    /**
     * @var WebhookHandlerInterface[]|iterable
     */
    private $handlers;

    public function __construct(iterable $handlers)
    {
        $this->handlers = $handlers;
    }

    public function handle(string $type, array $data): void
    {
        foreach ($this->handlers as $handler) {
            if ($handler->support($type)) {
                $handler->handle($data);

                return;
            }
        }
    }
}
