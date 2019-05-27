<?php

namespace AppBundle\Mailchimp\Webhook\Handler;

use AppBundle\Mailchimp\Webhook\EventTypeEnum;
use AppBundle\Membership\AdherentChangeEmailHandler;

class EmailUpdateHandler extends AbstractAdherentHandler
{
    private $adherentChangeEmailHandler;

    public function __construct(AdherentChangeEmailHandler $adherentChangeEmailHandler)
    {
        $this->adherentChangeEmailHandler = $adherentChangeEmailHandler;
    }

    public function handle(array $data): void
    {
        if ($adherent = $this->getAdherent($data['old_email'])) {
            $this->adherentChangeEmailHandler->handleRequest($adherent, $data['new_email']);
        }
    }

    public function support(string $type): bool
    {
        return EventTypeEnum::UPDATE_EMAIL === $type;
    }
}
