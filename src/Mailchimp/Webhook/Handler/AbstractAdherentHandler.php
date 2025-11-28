<?php

declare(strict_types=1);

namespace App\Mailchimp\Webhook\Handler;

use App\Entity\Adherent;
use App\Entity\SubscriptionType;
use App\Repository\AdherentRepository;
use App\Repository\SubscriptionTypeRepository;
use Symfony\Contracts\Service\Attribute\Required;

abstract class AbstractAdherentHandler extends AbstractHandler
{
    private AdherentRepository $adherentRepository;
    private SubscriptionTypeRepository $subscriptionTypeRepository;

    #[Required]
    public function setAdherentRepository(AdherentRepository $adherentRepository): void
    {
        $this->adherentRepository = $adherentRepository;
    }

    #[Required]
    public function setSubscriptionTypeRepository(SubscriptionTypeRepository $subscriptionTypeRepository): void
    {
        $this->subscriptionTypeRepository = $subscriptionTypeRepository;
    }

    public function support(string $type, string $listId): bool
    {
        return $listId === $this->mailchimpObjectIdMapping->getMainListId();
    }

    protected function getAdherent(string $email): ?Adherent
    {
        if (!$adherent = $this->adherentRepository->findOneByEmail($email)) {
            return null;
        }

        $this->entityManager->refresh($adherent);

        return $adherent;
    }

    protected function calculateNewSubscriptionTypes(array $adherentSubscriptionTypeCodes, array $mcLabels): array
    {
        /** @var SubscriptionType $subscriptionType */
        foreach ($this->subscriptionTypeRepository->findAll() as $subscriptionType) {
            if (\in_array($subscriptionType->getLabel(), $mcLabels, true) && !\in_array($subscriptionType->getCode(), $adherentSubscriptionTypeCodes, true)) {
                $adherentSubscriptionTypeCodes[] = $subscriptionType->getCode();
            } elseif (!\in_array($subscriptionType->getLabel(), $mcLabels, true) && \in_array($subscriptionType->getCode(), $adherentSubscriptionTypeCodes, true)) {
                unset($adherentSubscriptionTypeCodes[array_search($subscriptionType->getCode(), $adherentSubscriptionTypeCodes, true)]);
            }
        }

        return $adherentSubscriptionTypeCodes;
    }
}
