<?php

declare(strict_types=1);

namespace App\Committee;

use App\Admin\Committee\CommitteeAdherentMandateTypeEnum;
use App\Committee\DTO\CommitteeAdherentMandateCommand;
use App\Committee\Exception\CommitteeAdherentMandateException;
use App\Entity\Adherent;
use App\Entity\AdherentMandate\AdherentMandateInterface;
use App\Entity\AdherentMandate\CommitteeAdherentMandate;
use App\Entity\Committee;
use App\Repository\AdherentMandate\CommitteeAdherentMandateRepository;
use App\Repository\ElectedRepresentative\ElectedRepresentativeRepository;
use App\ValueObject\Genders;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class CommitteeAdherentMandateManager
{
    public const CREATE_ACTION = 'create';
    public const FINISH_ACTION = 'finish';
    public const ACTIONS = [
        self::CREATE_ACTION,
        self::FINISH_ACTION,
    ];

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly CommitteeAdherentMandateRepository $mandateRepository,
        private readonly ElectedRepresentativeRepository $electedRepresentativeRepository,
        private readonly CommitteeMembershipManager $committeeMembershipManager,
        private readonly TranslatorInterface $translator,
    ) {
    }

    public function createMandate(Adherent $adherent, Committee $committee): void
    {
        $this->checkGender($adherent);

        if ($activeMandate = $this->mandateRepository->findActiveMandate($adherent, $committee)) {
            $this->throwException(
                'adherent_mandate.committee.adherent_with_active_mandate',
                [
                    'email' => $adherent->getEmailAddress(),
                    'committee' => $activeMandate->getCommittee(),
                ]
            );
        }

        if ((Genders::FEMALE === $adherent->getGender() && $committee->hasFemaleAdherentMandate())
            || (Genders::MALE === $adherent->getGender() && $committee->hasMaleAdherentMandate())) {
            $this->throwException(
                'adherent_mandate.committee.committee_has_already_active_mandate',
                [
                    'committee' => $committee,
                    'gender' => $adherent->getGender(),
                ]
            );
        }

        $committee->addAdherentMandate($mandate = CommitteeAdherentMandate::createForCommittee($committee, $adherent));

        $this->entityManager->persist($mandate);
        $this->entityManager->flush();
    }

    public function endMandate(Adherent $adherent, Committee $committee): void
    {
        $mandate = $this->mandateRepository->findActiveMandateFor($adherent, $committee);

        if (!$mandate) {
            throw new CommitteeAdherentMandateException(\sprintf('Adherent with id "%s" (%s) has no active mandate in committee "%s"', $adherent->getId(), $adherent->getEmailAddress(), $committee->getName()));
        }

        $mandate->end(new \DateTime(), AdherentMandateInterface::REASON_MANUAL);

        $this->entityManager->flush();
    }

    public function replaceMandate(
        CommitteeAdherentMandate $mandate,
        CommitteeAdherentMandateCommand $command,
    ): CommitteeAdherentMandate {
        $adherent = $command->getAdherent();
        $committee = $command->getCommittee();
        $newMandate = CommitteeAdherentMandate::createFromCommand($command);

        if (!$adherent->getMembershipFor($committee)) {
            $this->committeeMembershipManager->followCommittee($adherent, $committee, CommitteeMembershipTriggerEnum::MANUAL);
        }

        $mandate->end(new \DateTime(), AdherentMandateInterface::REASON_REPLACED);

        $this->entityManager->persist($newMandate);
        $this->entityManager->flush();

        return $newMandate;
    }

    public function createMandateFromCommand(CommitteeAdherentMandateCommand $mandateCommand): CommitteeAdherentMandate
    {
        $newMandate = CommitteeAdherentMandate::createFromCommand($mandateCommand);
        $adherent = $mandateCommand->getAdherent();
        $committee = $mandateCommand->getCommittee();

        if (!$adherent->getMembershipFor($committee)) {
            $this->committeeMembershipManager->followCommittee($adherent, $committee, CommitteeMembershipTriggerEnum::MANUAL);
        }

        $this->entityManager->persist($newMandate);
        $this->entityManager->flush();

        return $newMandate;
    }

    public function checkAdherentForMandateReplacement(Adherent $adherent, string $gender): void
    {
        if ($adherent->getGender() !== $gender) {
            $this->throwException('adherent_mandate.committee.inappropriate_gender');
        }

        if ($adherent->isMinor()
            || $this->electedRepresentativeRepository->hasActiveParliamentaryMandate($adherent)) {
            $this->throwException('adherent_mandate.committee.adherent.not_valid');
        }
    }

    public function getAvailableMandateTypesFor(Committee $committee): array
    {
        return array_diff(
            CommitteeAdherentMandateTypeEnum::getTypesForCreation(),
            array_map(function (CommitteeAdherentMandate $mandate) {
                return $mandate->getType();
            }, $this->mandateRepository->findAllActiveMandatesForCommittee($committee)));
    }

    public function hasAvailableMandateTypesFor(Committee $committee): bool
    {
        return \count($this->getAvailableMandateTypesFor($committee)) > 0;
    }

    private function checkGender(Adherent $adherent): void
    {
        if (!\in_array($adherent->getGender(), Genders::MALE_FEMALE)) {
            $this->throwException(
                'adherent_mandate.committee.not_valid_gender',
                [
                    'email' => $adherent->getEmailAddress(),
                    'gender' => $adherent->getGender(),
                ]
            );
        }
    }

    private function throwException(string $msgId, array $variables = []): void
    {
        throw new CommitteeAdherentMandateException($this->translator->trans($msgId, $variables));
    }
}
