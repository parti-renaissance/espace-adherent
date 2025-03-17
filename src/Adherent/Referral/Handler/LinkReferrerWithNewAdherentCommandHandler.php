<?php

namespace App\Adherent\Referral\Handler;

use App\Adherent\Referral\Command\LinkReferrerWithNewAdherentCommand;
use App\Adherent\Referral\StatusEnum;
use App\Adherent\Referral\TypeEnum;
use App\Entity\Adherent;
use App\Entity\Referral;
use App\Repository\AdherentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class LinkReferrerWithNewAdherentCommandHandler
{
    public function __construct(
        private readonly AdherentRepository $adherentRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(LinkReferrerWithNewAdherentCommand $command): void
    {
        /** @var Adherent $adherent */
        if (!$adherent = $this->adherentRepository->findOneByUuid($command->getUuid())) {
            return;
        }

        if (!$referrer = $this->adherentRepository->findByPublicId($command->referrerPublicId, true)) {
            return;
        }

        $this->entityManager->persist($referral = Referral::createForReferred($adherent));
        $referral->status = StatusEnum::ACCOUNT_CREATED;
        $referral->type = TypeEnum::LINK;
        $referral->referrer = $referrer;

        $this->entityManager->flush();
    }
}
