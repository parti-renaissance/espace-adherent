<?php

namespace App\Mailchimp\Webhook\Handler;

use App\Entity\Adherent;
use App\Mailchimp\MailchimpSubscriptionLabelMapping;
use App\Repository\AdherentRepository;
use Symfony\Contracts\Service\Attribute\Required;

abstract class AbstractAdherentHandler extends AbstractHandler
{
    private AdherentRepository $adherentRepository;

    #[Required]
    public function setAdherentRepository(AdherentRepository $adherentRepository): void
    {
        $this->adherentRepository = $adherentRepository;
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
        foreach (MailchimpSubscriptionLabelMapping::getMapping() as $label => $code) {
            if (\in_array($label, $mcLabels, true) && !\in_array($code, $adherentSubscriptionTypeCodes, true)) {
                $adherentSubscriptionTypeCodes[] = $code;
            } elseif (!\in_array($label, $mcLabels, true) && \in_array($code, $adherentSubscriptionTypeCodes, true)) {
                unset($adherentSubscriptionTypeCodes[array_search($code, $adherentSubscriptionTypeCodes, true)]);
            }
        }

        return $adherentSubscriptionTypeCodes;
    }
}
