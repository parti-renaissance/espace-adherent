<?php

namespace App\Committee;

use App\Committee\Exception\CommitteeAdherentMandateException;
use App\Entity\Adherent;
use App\Entity\AdherentMandate\CommitteeAdherentMandate;
use App\Entity\Committee;
use App\Repository\AdherentMandate\CommitteeAdherentMandateRepository;
use App\ValueObject\Genders;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Translation\TranslatorInterface;

class CommitteeAdherentMandateManager
{
    public const CREATE_ACTION = 'create';
    public const FINISH_ACTION = 'finish';
    public const ACTIONS = [
        self::CREATE_ACTION,
        self::FINISH_ACTION,
    ];

    /** @var EntityManagerInterface */
    private $entityManager;
    /** @var CommitteeAdherentMandateRepository */
    private $mandateRepository;
    /** @var TranslatorInterface */
    private $translator;

    public function __construct(
        EntityManagerInterface $entityManager,
        CommitteeAdherentMandateRepository $mandateRepository,
        TranslatorInterface $translator
    ) {
        $this->entityManager = $entityManager;
        $this->mandateRepository = $mandateRepository;
        $this->translator = $translator;
    }

    public function createMandate(Adherent $adherent, Committee $committee): void
    {
        if (!\in_array($adherent->getGender(), Genders::MALE_FEMALE)) {
            $this->throwException(
                'adherent_mandate.committee.not_valid_gender',
                [
                    '{{ email }}' => $adherent->getEmailAddress(),
                    '{{ gender }}' => $adherent->getGender(),
                ]
            );
        }

        if ($activeMandate = $this->mandateRepository->findActiveMandate($adherent, $committee)) {
            $this->throwException(
                'adherent_mandate.committee.adherent_with_active_mandate',
                [
                    '{{ email }}' => $adherent->getEmailAddress(),
                    '{{ committee }}' => $activeMandate->getCommittee(),
                ]
            );
        }

        if ($adherent->hasTerritorialCouncilMembership()) {
            $this->throwException(
                'adherent_mandate.adherent_has_territorial_council_membership',
                [
                    '{{ email }}' => $adherent->getEmailAddress(),
                    '{{ territorialCouncil }}' => $committee,
                ]
            );
        }

        if ((Genders::FEMALE === $adherent->getGender() && $committee->hasFemaleMandate())
            || (Genders::MALE === $adherent->getGender() && $committee->hasMaleMandate())) {
            $this->throwException(
                'adherent_mandate.committee.committee_has_already_active_mandate',
                [
                    '{{ committee }}' => $committee,
                    '{{ gender }}' => $adherent->getGender(),
                ]
            );
        }

        $mandate = new CommitteeAdherentMandate($adherent, $adherent->getGender(), $committee, new \DateTime());
        $committee->addAdherentMandate($mandate);

        $this->entityManager->persist($mandate);
        $this->entityManager->flush();
    }

    public function endMandate(Adherent $adherent, Committee $committee): void
    {
        $mandate = $this->mandateRepository->findActiveMandateFor($adherent, $committee);

        if (!$mandate) {
            throw new CommitteeAdherentMandateException(\sprintf('Adherent with id "%s" (%s) has no active mandate in committee "%s"', $adherent->getId(), $adherent->getEmailAddress(), $committee->getName()));
        }

        $mandate->setFinishAt(new \DateTime());

        $this->entityManager->flush();
    }

    private function throwException(string $msgId, array $variables): void
    {
        throw new CommitteeAdherentMandateException($this->translator->trans($msgId, $variables));
    }
}
