<?php

namespace App\TerritorialCouncil\Handlers;

use App\Entity\Adherent;
use App\Entity\TerritorialCouncil\TerritorialCouncil;
use App\Entity\TerritorialCouncil\TerritorialCouncilMembership;
use App\Entity\TerritorialCouncil\TerritorialCouncilMembershipLog;
use App\Entity\TerritorialCouncil\TerritorialCouncilQuality;
use App\Entity\TerritorialCouncil\TerritorialCouncilQualityEnum;
use App\Repository\TerritorialCouncil\TerritorialCouncilRepository;
use App\TerritorialCouncil\Event\MembershipEvent;
use App\TerritorialCouncil\Events;
use App\TerritorialCouncil\PoliticalCommitteeManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

abstract class AbstractTerritorialCouncilHandler implements TerritorialCouncilMembershipHandlerInterface
{
    private $em;
    private $dispatcher;
    protected $repository;
    /** @var PoliticalCommitteeManager */
    protected $politicalCommitteeManager;

    public function __construct(
        EntityManagerInterface $em,
        TerritorialCouncilRepository $repository,
        EventDispatcherInterface $dispatcher,
        PoliticalCommitteeManager $politicalCommitteeManager
    ) {
        $this->em = $em;
        $this->repository = $repository;
        $this->dispatcher = $dispatcher;
        $this->politicalCommitteeManager = $politicalCommitteeManager;
    }

    /**
     * Should be true, because if adherent satisfy condition to be a member of territorial council,
     * he should be added, if not we should remove concerned quality from membership.
     */
    public function supports(Adherent $adherent): bool
    {
        return true;
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

            // if has candidacy, we should keep a quality in the membership
            if ($actualMembership->hasCandidacies()) {
                // we need to log that adherent has no more quality in membership, but has a candidacy
                $this->log(
                    'warning',
                    $adherent,
                    $actualMembership,
                    [],
                    'Cette qualité doit être retirée, mais l\'adhérent a une candidature dans ce conseil territorial.'
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
            $this->politicalCommitteeManager->addPoliticalCommitteeQuality($adherent, $qualityName, true);
            $this->em->flush();

            return;
        }

        $highestPriority = $actualMembership->getHighestQualityPriority();
        // do nothing because actual membership has higher priority
        if (TerritorialCouncilQualityEnum::QUALITY_PRIORITIES[$qualityName] > $highestPriority) {
            return;
        }

        // we need to remove actual membership and create a new one for actual quality
        if (TerritorialCouncilQualityEnum::QUALITY_PRIORITIES[$qualityName] < $highestPriority) {
            if ($actualMembership->hasCandidacies()) {
                // we need to log that adherent has no more quality in membership, but has a candidacy
                $this->log(
                    'warning',
                    $adherent,
                    $actualMembership,
                    [$actualMembership->getTerritorialCouncil(), $territorialCouncil],
                    'Cette qualité doit être retirée, mais l\'adhérent a une candidature dans ce conseil territorial.'
                );

                return;
            }

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

            return;
        }

        // if the same quality priority, we need to log about a problem
        $this->log(
            'warning',
            $adherent,
            $actualMembership,
            [$territorialCouncil],
            'Adhérent est déjà membre avec cette qualité (de priorité majeure)'
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
        $membership = new TerritorialCouncilMembership($territorialCouncil, $adherent);
        $membership->addQuality($quality);
        $adherent->setTerritorialCouncilMembership($membership);

        $this->em->persist($membership);

        // add Political committee member
        if (\in_array($quality->getName(), TerritorialCouncilQualityEnum::POLITICAL_COMMITTEE_OFFICIO_MEMBERS)) {
            $pcMembership = $this->politicalCommitteeManager->createMembership(
                $adherent,
                $territorialCouncil->getPoliticalCommittee(),
                $this->getQualityName()
            );
            $adherent->setPoliticalCommitteeMembership($pcMembership);

            $this->em->persist($pcMembership);
        }

        $this->em->flush();

        $this->dispatcher->dispatch(Events::TERRITORIAL_COUNCIL_MEMBERSHIP_CREATE, new MembershipEvent($adherent, $territorialCouncil));
    }

    protected function removeMembership(Adherent $adherent, TerritorialCouncil $council): void
    {
        $adherent->setTerritorialCouncilMembership(null);
        $adherent->revokePoliticalCommitteeMembership();
        $this->em->flush();

        $this->dispatcher->dispatch(Events::TERRITORIAL_COUNCIL_MEMBERSHIP_REMOVE, new MembershipEvent($adherent, $council));
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
}
