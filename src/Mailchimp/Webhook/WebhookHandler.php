<?php

namespace AppBundle\Mailchimp\Webhook;

use AppBundle\Mailchimp\Webhook\Handler\WebhookHandlerInterface;

class WebhookHandler
{
    /**
     * @var WebhookHandlerInterface[]|iterable
     */
    private $handlers;
    private $listId;

    public function __construct(iterable $handlers, string $listId)
    {
        $this->handlers = $handlers;
        $this->listId = $listId;
    }

    public function __invoke(string $type, array $data): void
    {
        if (!isset($data['list_id']) || $data['list_id'] !== $this->listId) {
            return;
        }

        foreach ($this->handlers as $handler) {
            if ($handler->support($type)) {
                $handler->handle($data);

                return;
            }
        }
    }
}
