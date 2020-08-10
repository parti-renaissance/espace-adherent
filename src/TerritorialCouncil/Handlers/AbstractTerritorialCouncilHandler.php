<?php

namespace App\TerritorialCouncil\Handlers;

use App\Entity\Adherent;
use App\Entity\TerritorialCouncil\TerritorialCouncil;
use App\Entity\TerritorialCouncil\TerritorialCouncilMembership;
use App\Entity\TerritorialCouncil\TerritorialCouncilQuality;
use App\Entity\TerritorialCouncil\TerritorialCouncilQualityEnum;
use App\Repository\TerritorialCouncil\TerritorialCouncilRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

abstract class AbstractTerritorialCouncilHandler implements TerritorialCouncilMembershipHandlerInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    /** @var EntityManagerInterface */
    protected $em;
    /** @var TerritorialCouncilRepository */
    protected $repository;

    public function __construct(EntityManagerInterface $em, TerritorialCouncilRepository $repository)
    {
        $this->em = $em;
        $this->repository = $repository;
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
                'Plusieurs conseils territorials ont été trouvés pour cette qualité'
            );

            return;
        }

        // if no territorial council found, we need to remove concerned membership quality if exist
        if (0 === $count) {
            if (!$adherent->hasTerritorialCouncilMembership()) {
                return;
            }

            if (!$adherent->getTerritorialCouncilMembership()->hasQuality($qualityName)) {
                return;
            }

            $adherent->getTerritorialCouncilMembership()->removeQualityWithName($qualityName);
            $this->em->flush();

            return;
        }

        $territorialCouncil = $territorialCouncils[0];
        $quality = new TerritorialCouncilQuality($qualityName, $this->getQualityZone($adherent));
        if (!$adherent->hasTerritorialCouncilMembership()) {
            // create a new territorial council membership with quality
            $this->addTerritorialCouncilMembership($adherent, $territorialCouncil, $quality);
            $this->em->flush();

            return;
        }

        $actualMembership = $adherent->getTerritorialCouncilMembership();
        // if the adherent has a membership in the same territorial council, just add quality if it doesn't exist
        if ($territorialCouncil->getId() === $actualMembership->getTerritorialCouncil()->getId()) {
            $actualMembership->addQuality($quality);
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
            $this->addTerritorialCouncilMembership($adherent, $territorialCouncil, $quality);
            $this->em->flush();

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

    private function addTerritorialCouncilMembership(
        Adherent $adherent,
        TerritorialCouncil $territorialCouncil,
        TerritorialCouncilQuality $quality
    ): void {
        $membership = new TerritorialCouncilMembership($territorialCouncil, $adherent);
        $membership->addQuality($quality);
        $adherent->setTerritorialCouncilMembership($membership);

        $this->em->persist($membership);
        $this->em->flush();
    }

    private function log(
        string $level,
        Adherent $adherent,
        ?TerritorialCouncilMembership $membership,
        array $territorialCouncils,
        string $message
    ): void {
        $msg = \sprintf(
            '%s | %s | %s | %s | %s | %s | %s',
            $adherent->getId(),
            $adherent->getEmailAddress(),
            $this->getQualityName(),
            $membership ? $membership->getId() : '',
            $membership ? (string) $membership->getTerritorialCouncil() : '',
            implode(',', $territorialCouncils),
            $message
        );

        $this->logger->$level($msg);
    }
}
