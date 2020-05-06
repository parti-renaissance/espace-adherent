<?php

namespace App\Mailchimp\Webhook\Handler;

use App\Mailchimp\Webhook\EventTypeEnum;
use Doctrine\Common\Persistence\ObjectManager;

class AdherentSubscribeHandler extends AbstractAdherentHandler
{
    private $em;

    public function __construct(ObjectManager $em)
    {
        $this->em = $em;
    }

    public function handle(array $data): void
    {
        if ($adherent = $this->getAdherent($data['email'])) {
            $adherent->setEmailUnsubscribed(false);

            $this->em->flush();
        }
    }

    public function support(string $type, string $listId): bool
    {
        return EventTypeEnum::SUBSCRIBE === $type && parent::support($type, $listId);
    }
}
