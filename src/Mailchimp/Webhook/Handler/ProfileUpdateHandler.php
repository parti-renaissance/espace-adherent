<?php

namespace AppBundle\Mailchimp\Webhook\Handler;

use AppBundle\Entity\Adherent;
use AppBundle\Mailchimp\Webhook\EventTypeEnum;
use AppBundle\Repository\AdherentRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;

class ProfileUpdateHandler implements WebhookHandlerInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    private $adherentRepository;
    private $listId;
    private $interests;

    public function __construct(
        ObjectManager $manager,
        AdherentRepository $adherentRepository,
        string $listId,
        array $interests,
        LoggerInterface $logger
    ) {
        $this->manager = $manager;
        $this->adherentRepository = $adherentRepository;
        $this->interests = $interests;
        $this->listId = $listId;
        $this->logger = $logger;
    }

    public function handle(array $data): void
    {
        if (!isset($data['list_id']) || $data['list_id'] !== $this->listId) {
            return;
        }

        if ($adherent = $this->adherentRepository->findOneByEmail($data['email'])) {
            $mergeFields = $data['merges'] ?? [];

            $this->updateInterests($adherent, $mergeFields['INTERESTS'] ?? '');

            $this->logger->error('[Mailchimp Webhook] debug to remove', ['data' => $groupings = $mergeFields['GROUPINGS'] ?? [], 'data_string' => json_encode($groupings)]);

            $this->manager->flush();
        }
    }

    public function support(string $type): bool
    {
        return EventTypeEnum::UPDATE_PROFILE === $type;
    }

    private function updateInterests(Adherent $adherent, string $interestLabels): void
    {
        $interestLabels = explode(', ', $interestLabels);

        if (empty($interestLabels)) {
            $adherent->setInterests([]);

            return;
        }

        foreach ($interestLabels as $label) {
            if (false === array_search($label, $this->interests, true)) {
                $this->logger->error(sprintf(
                    '[MailchimpWebhook] Mailchimp interest label "%s" does not match any EM interest labels',
                    $label
                ));

                return;
            }
        }

        $adherent->setInterests(array_keys(array_intersect($this->interests, $interestLabels)));
    }
}
