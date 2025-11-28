<?php

declare(strict_types=1);

namespace App\Mailchimp\Webhook\Handler;

use App\Mailchimp\Webhook\EventTypeEnum;
use App\Membership\AdherentChangeEmailHandler;

class AdherentEmailUpdateHandler extends AbstractAdherentHandler
{
    private AdherentChangeEmailHandler $adherentChangeEmailHandler;

    public function __construct(AdherentChangeEmailHandler $adherentChangeEmailHandler)
    {
        $this->adherentChangeEmailHandler = $adherentChangeEmailHandler;
    }

    public function handle(array $data): void
    {
        if ($adherent = $this->getAdherent($data['old_email'])) {
            if ($adherent->isToDelete()) {
                return;
            }

            $this->adherentChangeEmailHandler->handleRequest($adherent, $data['new_email']);
        }
    }

    public function support(string $type, string $listId): bool
    {
        return EventTypeEnum::UPDATE_EMAIL === $type && parent::support($type, $listId);
    }
}
