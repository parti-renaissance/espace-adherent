<?php

namespace App\Adherent\Referral\Handler;

use App\Adherent\Referral\Command\LinkReferrerWithNewAdherentCommand;
use App\Adherent\Referral\IdentifierGenerator;
use App\Adherent\Referral\Notifier;
use App\Adherent\Referral\StatusEnum;
use App\Adherent\Referral\TypeEnum;
use App\Entity\Adherent;
use App\Entity\Referral;
use App\Repository\AdherentRepository;
use App\Repository\ReferralRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class LinkReferrerWithNewAdherentCommandHandler
{
    public function __construct(
        private readonly AdherentRepository $adherentRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly ReferralRepository $referralRepository,
        private readonly Notifier $notifier,
        private readonly IdentifierGenerator $identifierGenerator,
    ) {
    }

    public function __invoke(LinkReferrerWithNewAdherentCommand $command): void
    {
        /** @var Adherent $adherent */
        if (!$adherent = $this->adherentRepository->findOneByUuid($command->getUuid())) {
            return;
        }

        if ($command->fromCotisation) {
            $this->referralRepository->finishReferralAdhesionStatus($adherent);

            if ($referral = $this->referralRepository->findFinishedForAdherent($adherent)) {
                $this->notifier->sendAdhesionFinishedMessage($referral);
            }

            return;
        }

        $referral = null;
        if ($command->referrerPublicId) {
            $referral = $this->createNewReferral($adherent, $command->referrerPublicId);
        } elseif ($command->referralIdentifier) {
            $referral = $this->updateExistingReferral($adherent, $command->referralIdentifier);
        }

        $this->referralRepository->updateReferralsStatus($adherent, $referral, StatusEnum::ADHESION_VIA_OTHER_LINK);
    }

    private function createNewReferral(Adherent $adherent, string $referrerPublicId): ?Referral
    {
        if (!$referrer = $this->adherentRepository->findByPublicId($referrerPublicId, true)) {
            return null;
        }

        $this->entityManager->persist($referral = Referral::createForReferred($adherent));
        $referral->identifier = $this->identifierGenerator->generate();
        $referral->status = StatusEnum::ACCOUNT_CREATED;
        $referral->type = TypeEnum::LINK;
        $referral->referrer = $referrer;

        $this->entityManager->flush();

        return $referral;
    }

    private function updateExistingReferral(Adherent $adherent, string $referralIdentifier): ?Referral
    {
        if (!$referral = $this->referralRepository->findByIdentifier($referralIdentifier)) {
            return null;
        }

        if ($referral->referred && $referral->referred->getId() !== $adherent->getId()) {
            $this->entityManager->persist($newReferral = Referral::createForReferred($adherent));
            $newReferral->status = StatusEnum::ACCOUNT_CREATED;
            $newReferral->type = $referral->type;
            $newReferral->referrer = $referral->referrer;

            $referral = $newReferral;
        }

        $referral->updateFromAdherent($adherent);

        if (!$referral->status || StatusEnum::INVITATION_SENT === $referral->status) {
            $referral->status = StatusEnum::ACCOUNT_CREATED;
        }

        $this->entityManager->flush();

        return $referral;
    }
}
