<?php

namespace AppBundle\Mailchimp\Webhook\Handler;

use AppBundle\Mailchimp\Webhook\EventTypeEnum;
use AppBundle\Membership\AdherentChangeEmailHandler;
use AppBundle\Repository\AdherentRepository;

class EmailUpdateHandler implements WebhookHandlerInterface
{
    private $adherentRepository;
    private $adherentChangeEmailHandler;
    private $listId;

    public function __construct(
        AdherentRepository $adherentRepository,
        AdherentChangeEmailHandler $adherentChangeEmailHandler,
        string $listId
    ) {
        $this->adherentRepository = $adherentRepository;
        $this->adherentChangeEmailHandler = $adherentChangeEmailHandler;
        $this->listId = $listId;
    }

    public function handle(array $data): void
    {
        if (!isset($data['list_id']) || $data['list_id'] !== $this->listId) {
            return;
        }

        if ($adherent = $this->adherentRepository->findOneByEmail($data['old_email'])) {
            $this->adherentChangeEmailHandler->handleRequest($adherent, $data['new_email']);
        }
    }

    public function support(string $type): bool
    {
        return EventTypeEnum::UPDATE_EMAIL === $type;
    }
}
