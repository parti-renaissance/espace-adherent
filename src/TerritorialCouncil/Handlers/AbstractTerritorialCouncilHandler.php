<?php

namespace App\TerritorialCouncil\Handlers;

use App\Entity\Adherent;
use App\Entity\TerritorialCouncil\TerritorialCouncil;
use App\Entity\TerritorialCouncil\TerritorialCouncilMembership;
use App\Entity\TerritorialCouncil\TerritorialCouncilMembershipLog;
use App\Entity\TerritorialCouncil\TerritorialCouncilQuality;
use App\Entity\TerritorialCouncil\TerritorialCouncilQualityEnum;
use App\Repository\AdherentMandate\CommitteeAdherentMandateRepository;
use App\Repository\AdherentMandate\TerritorialCouncilAdherentMandateRepository;
use App\Repository\TerritorialCouncil\TerritorialCouncilRepository;
use App\TerritorialCouncil\Event\MembershipEvent;
use App\TerritorialCouncil\Events;
use App\TerritorialCouncil\PoliticalCommitteeManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

abstract class AbstractTerritorialCouncilHandler implements TerritorialCouncilMembershipHandlerInterface
{
    private $em;
    private $dispatcher;
    protected $repository;
    /** @var PoliticalCommitteeManager */
    protected $politicalCommitteeManager;
    /** @var CommitteeAdherentMandateRepository */
    protected $committeeMandateRepository;
    /** @var TerritorialCouncilAdherentMandateRepository */
    protected $tcMandateRepository;
    private $eventDispatchingEnabled = true;

    public function __construct(
        EntityManagerInterface $em,
        TerritorialCouncilRepository $repository,
        EventDispatcherInterface $dispatcher,
        PoliticalCommitteeManager $politicalCommitteeManager,
        CommitteeAdherentMandateRepository $committeeMandateRepository,
        TerritorialCouncilAdherentMandateRepository $tcMandateRepository
    ) {
        $this->em = $em;
        $this->repository = $repository;
        $this->dispatcher = $dispatcher;
        $this->politicalCommitteeManager = $politicalCommitteeManager;
        $this->committeeMandateRepository = $committeeMandateRepository;
        $this->tcMandateRepository = $tcMandateRepository;
    }

    /**
     * Should be true, because if adherent satisfy condition to be a member of territorial council,
     * he should be added, if not we should remove concerned quality from membership.
     */
    public function supports(Adherent $adherent): bool
    {
        return true;
    }

    public function disableEventDispatching(): void
    {
        $this->eventDispatchingEnabled = false;
    }

    public function getPriority(): int
    {
        return TerritorialCouncilQualityEnum::QUALITY_PRIORITIES[$this->getQualityName()];
    }

    public function handle(Adherent $adherent): void
    {
        $territorialCouncils = $this->findTerritorialCouncils($adherent);
        $qualityName = $this->getQualityName();

        // if multiple territorial councils
        $count = \count($territorialCouncils);
        if ($count > 1) {
            if (!($actualMembership = $adherent->getTerritorialCouncilMembership())) {
                // we need to log because we cannot choose between found territorial councils
                $this->log(
                    'warning',
                    $adherent,
                    null,
                    $territorialCouncils,
                    'Plusieurs conseils territorials ont été trouvés pour cette qualité'
                );

                return;
            }

            $highestPriority = $actualMembership->getHighestQualityPriority();
            // do nothing because actual membership has higher priority
            if (TerritorialCouncilQualityEnum::QUALITY_PRIORITIES[$qualityName] > $highestPriority) {
                return;
            }

            // we need to log because we cannot choose between all territorial councils
            $this->log(
                'warning',
                $adherent,
                $actualMembership,
                $territorialCouncils,
                'Plusieurs conseils territorials ont été trouvés pour cette qualité et l\'adhérent a déjà cette qualité'
            );

            return;
        }

        // if no territorial council found, we need to remove concerned membership quality if exist
        if (0 === $count) {
            if (!($actualMembership = $adherent->getTerritorialCouncilMembership())) {
                return;
            }

            if (!$actualMembership->hasQuality($qualityName)) {
                return;
            }

            if ('' !== $msq = $this->getRemovingConstraintsMsg($qualityName, $adherent, $actualMembership)) {
                $this->log(
                    'warning',
                    $adherent,
                    $actualMembership,
                    $territorialCouncils,
                    'Cette qualité doit être retirée, mais '.$msq
                );

                return;
            }

            $adherent->getTerritorialCouncilMembership()->removeQualityWithName($qualityName);
            $this->politicalCommitteeManager->removePoliticalCommitteeQuality($adherent, $qualityName);

            $this->em->flush();

            return;
        }

        $territorialCouncil = $territorialCouncils[0];
        $quality = new TerritorialCouncilQuality($qualityName, $this->getQualityZone($adherent));
        if (!$adherent->hasTerritorialCouncilMembership()) {
            // create a new territorial council membership with quality
            $this->addMembership($adherent, $territorialCouncil, $quality);

            return;
        }

        $actualMembership = $adherent->getTerritorialCouncilMembership();
        // if the adherent has a membership in the same territorial council, just add quality if it doesn't exist
        if ($territorialCouncil->getId() === $actualMembership->getTerritorialCouncil()->getId()) {
            $actualMembership->addQuality($quality);
            if ($this->politicalCommitteeManager->canAddQuality($qualityName, $adherent)) {
                $this->politicalCommitteeManager->addPoliticalCommitteeQuality($adherent, $qualityName);
            }
            $this->em->flush();

            return;
        }

        $highestPriority = $actualMembership->getHighestQualityPriority();
        // do nothing because actual membership has higher priority
        if (TerritorialCouncilQualityEnum::QUALITY_PRIORITIES[$qualityName] > $highestPriority) {
            return;
        }

        // we check if no quality with removing constraints
        $msg = '';
        foreach ($actualMembership->getQualities() as $quality) {
            $constraintMsg = $this->getRemovingConstraintsMsg($quality->getName(), $adherent, $actualMembership);
            if ('' !== $constraintMsg && false === strpos($msg, $constraintMsg)) {
                $msg .= $this->getRemovingConstraintsMsg($quality->getName(), $adherent, $actualMembership);
            }
        }

        if ('' !== $msg) {
            $this->log(
                'warning',
                $adherent,
                $actualMembership,
                [$territorialCouncil],
                'Le changement du conseil territorial n\'est pas possible : '.$msg
            );

            return;
        }

        // we need to remove actual membership and create a new one for actual quality
        $this->removeMembership($adherent, $actualMembership->getTerritorialCouncil());
        $this->addMembership($adherent, $territorialCouncil, $quality);

        // we need to log a change of a territorial council membership
        $this->log(
            'info',
            $adherent,
            $actualMembership,
            [$territorialCouncil],
            'Adhérent a changé le conseil territorial'
        );
    }

    abstract protected function findTerritorialCouncils(Adherent $adherent): array;

    abstract protected function getQualityName(): string;

    abstract protected function getQualityZone(Adherent $adherent): string;

    private function addMembership(
        Adherent $adherent,
        TerritorialCouncil $territorialCouncil,
        TerritorialCouncilQuality $quality
    ): void {
        $qualityName = $quality->getName();
        $membership = new TerritorialCouncilMembership($territorialCouncil, $adherent);
        $membership->addQuality($quality);
        $adherent->setTerritorialCouncilMembership($membership);

        $this->em->persist($membership);
        $this->em->flush();

        // add Political committee member
        if ($this->politicalCommitteeManager->canAddQuality($qualityName, $adherent)) {
            $pcMembership = $this->politicalCommitteeManager->createMembership(
                $adherent,
                $territorialCouncil->getPoliticalCommittee(),
               $qualityName
            );

            $this->em->persist($pcMembership);
            $this->em->flush();
        }

        $this->dispatch(Events::TERRITORIAL_COUNCIL_MEMBERSHIP_CREATE, new MembershipEvent($adherent, $territorialCouncil));
    }

    protected function removeMembership(Adherent $adherent, TerritorialCouncil $council): void
    {
        $adherent->setTerritorialCouncilMembership(null);
        $adherent->revokePoliticalCommitteeMembership();
        $this->em->flush();

        $this->dispatch(Events::TERRITORIAL_COUNCIL_MEMBERSHIP_REMOVE, new MembershipEvent($adherent, $council));
    }

    private function getRemovingConstraintsMsg(
        string $qualityName,
        Adherent $adherent,
        TerritorialCouncilMembership $actualMembership
    ): string {
        // if has a candidacy
        if (\in_array($qualityName, TerritorialCouncilQualityEnum::POLITICAL_COMMITTEE_ELECTED_MEMBERS)
            && ($election = $actualMembership->getTerritorialCouncil()->getCurrentElection())
            && $election->isOngoing()
            && $actualMembership->getCandidacyForElection($election)) {
            return 'l\'adhérent a une candidature dans ce conseil territorial.';
        }

        // if has a committee mandate
        if (
            TerritorialCouncilQualityEnum::ELECTED_CANDIDATE_ADHERENT === $qualityName
            && $adherent->hasTerritorialCouncilMembership()
            && ($committeeMandate = $this->committeeMandateRepository->findActiveMandateInTerritorialCouncil(
                $adherent,
                $actualMembership->getTerritorialCouncil()
            ))
            && null === $committeeMandate->getQuality()
        ) {
            return \sprintf(
                'l\'adhérent a un mandat dans ce conseil territorial, dans le comité "%s".',
                $committeeMandate->getCommittee()->getName()
            );
        }

        // if has a CoPol mandate
        if (
            \in_array($qualityName, TerritorialCouncilQualityEnum::ABLE_TO_CANDIDATE, true)
            && $adherent->hasTerritorialCouncilMembership()
            && $this->tcMandateRepository->findActiveMandateWithQuality(
                $adherent,
                $actualMembership->getTerritorialCouncil(),
                $qualityName
            )
        ) {
            return 'l\'adhérent a un mandat avec cette qualité dans le CoPol.';
        }

        // if has a mayor or leader Political committee quality
        if (\in_array($qualityName, [TerritorialCouncilQualityEnum::MAYOR, TerritorialCouncilQualityEnum::CITY_COUNCILOR])
            && $adherent->hasPoliticalCommitteeMembership()
            && $adherent->getPoliticalCommitteeMembership()->hasOneOfQualities([TerritorialCouncilQualityEnum::MAYOR, TerritorialCouncilQualityEnum::LEADER])) {
            return 'l\'adhérent a une qualité "Maire" ou "Président(e) du groupe d\'opposition LaREM" dans le comité politique';
        }

        return '';
    }

    private function log(
        string $level,
        Adherent $adherent,
        ?TerritorialCouncilMembership $membership,
        array $territorialCouncils,
        string $message
    ): void {
        $log = new TerritorialCouncilMembershipLog(
            $level,
            $message,
            $adherent,
            $this->getQualityName(),
            $membership ? $membership->getTerritorialCouncil() : null,
            $membership ? \array_map(function (TerritorialCouncilQuality $quality) {
                return $quality->getName();
            }, $membership->getQualities()->toArray()) : [],
            $territorialCouncils ? \array_map(function (TerritorialCouncil $territorialCouncil) {
                return $territorialCouncil->getNameCodes();
            }, $territorialCouncils) : []
        );

        $this->em->persist($log);
        $this->em->flush();
    }

    private function dispatch(string $eventName, MembershipEvent $event): void
    {
        if ($this->eventDispatchingEnabled) {
            $this->dispatcher->dispatch($event, $eventName);
        }
    }
}
