<?php

namespace AppBundle\Mailchimp\Webhook\Handler;

use AppBundle\Entity\Adherent;
use AppBundle\Mailchimp\Campaign\MailchimpObjectIdMapping;
use AppBundle\Mailchimp\MailchimpSubscriptionLabelMapping;
use AppBundle\Repository\AdherentRepository;

abstract class AbstractAdherentHandler implements WebhookHandlerInterface
{
    /** @var AdherentRepository */
    private $repository;

    /** @var MailchimpObjectIdMapping */
    protected $mailchimpObjectIdMapping;

    /**
     * @required
     */
    public function setRepository(AdherentRepository $repository): void
    {
        $this->repository = $repository;
    }

    /**
     * @required
     */
    public function setMailchimpObjectIdMapping(MailchimpObjectIdMapping $mailchimpObjectIdMapping): void
    {
        $this->mailchimpObjectIdMapping = $mailchimpObjectIdMapping;
    }

    public function support(string $type, string $listId): bool
    {
        return $listId === $this->mailchimpObjectIdMapping->getMainListId();
    }

    protected function getAdherent(string $email): ?Adherent
    {
        return $this->repository->findOneByEmail($email);
    }

    protected function calculateNewSubscriptionTypes(array $adherentSubscriptionTypeCodes, array $mcLabels): array
    {
        foreach (MailchimpSubscriptionLabelMapping::getMapping() as $label => $code) {
            if (\in_array($label, $mcLabels, true) && !\in_array($code, $adherentSubscriptionTypeCodes, true)) {
                $userSubscriptionTypeCodes[] = $code;
            } elseif (!\in_array($label, $mcLabels, true) && \in_array($code, $adherentSubscriptionTypeCodes, true)) {
                unset($adherentSubscriptionTypeCodes[array_search($code, $adherentSubscriptionTypeCodes, true)]);
            }
        }

        return $adherentSubscriptionTypeCodes;
    }
}
