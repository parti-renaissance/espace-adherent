<?php

namespace App\SmsCampaign\Handler;

use App\Entity\SmsCampaign;
use App\OvhCloud\Notifier;
use App\Repository\AdherentRepository;
use App\SmsCampaign\Command\SendSmsCampaignCommand;
use App\SmsCampaign\SmsCampaignStatusEnum;
use App\Utils\PhoneNumberUtils;
use Doctrine\ORM\EntityManagerInterface;
use libphonenumber\PhoneNumberFormat;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;

class SendSmsCampaignCommandHandler implements MessageHandlerInterface
{
    private $entityManager;
    private $adherentRepository;
    private $notifier;

    public function __construct(
        EntityManagerInterface $entityManager,
        AdherentRepository $adherentRepository,
        Notifier $notifier
    ) {
        $this->entityManager = $entityManager;
        $this->adherentRepository = $adherentRepository;
        $this->notifier = $notifier;
    }

    public function __invoke(SendSmsCampaignCommand $command): void
    {
        /** @var SmsCampaign $smsCampaign */
        if (!$smsCampaign = $this->entityManager->find(SmsCampaign::class, $command->getSmsCampaignId())) {
            return;
        }

        if (!$smsCampaign->isSending()) {
            return;
        }

        $phones = [];
        $count = 0;

        $query = $this->adherentRepository->createQueryBuilderForSmsCampaign($smsCampaign)
            ->setMaxResults(500)
            ->getQuery()
        ;

        while ($adherents = $query->getResult()) {
            foreach ($adherents as $adherent) {
                $phones[] = PhoneNumberUtils::format($adherent->getPhone(), PhoneNumberFormat::E164);
            }
            $count += \count($adherents);
            $query->setFirstResult($count);
        }

        try {
            $response = $this->notifier->sendSmsCampaign($smsCampaign, array_unique($phones));

            $smsCampaign->setResponsePayload($response->getContent());
            $responseData = $response->toArray();

            if (empty($responseData['id'])) {
                $smsCampaign->setStatus(SmsCampaignStatusEnum::ERROR);
            } else {
                $smsCampaign->setStatus(SmsCampaignStatusEnum::DONE);
                $smsCampaign->setExternalId(Uuid::fromString($responseData['id']));
            }
        } catch (HttpExceptionInterface $e) {
            $smsCampaign->setResponsePayload($e->getResponse()->getContent(false));
            $smsCampaign->setStatus(SmsCampaignStatusEnum::ERROR);
        }

        $this->entityManager->flush();
    }
}
