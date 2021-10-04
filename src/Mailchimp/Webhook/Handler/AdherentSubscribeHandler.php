<?php

namespace App\Mailchimp\Webhook\Handler;

use App\Mailchimp\Webhook\EventTypeEnum;
use App\Repository\SubscriptionTypeRepository;
use App\Subscription\SubscriptionTypeEnum;
use Doctrine\ORM\EntityManagerInterface;

class AdherentSubscribeHandler extends AbstractAdherentHandler
{
    private EntityManagerInterface $em;
    private SubscriptionTypeRepository $subscriptionTypeRepository;

    public function __construct(EntityManagerInterface $em, SubscriptionTypeRepository $subscriptionTypeRepository)
    {
        $this->em = $em;
        $this->subscriptionTypeRepository = $subscriptionTypeRepository;
    }

    public function handle(array $data): void
    {
        if ($adherent = $this->getAdherent($data['email'])) {
            $adherent->setEmailUnsubscribed(false);

            $this->subscriptionTypeRepository->addToAdherent($adherent, SubscriptionTypeEnum::DEFAULT_EMAIL_TYPES);

            $this->em->flush();
        }
    }

    public function support(string $type, string $listId): bool
    {
        return EventTypeEnum::SUBSCRIBE === $type && parent::support($type, $listId);
    }
}
