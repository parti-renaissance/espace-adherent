<?php

namespace App\Command;

use App\Entity\Adherent;
use App\Entity\TerritorialCouncil\TerritorialCouncilMembership;
use App\Entity\TerritorialCouncil\TerritorialCouncilQuality;
use App\Entity\TerritorialCouncil\TerritorialCouncilQualityEnum;
use App\Repository\AdherentRepository;
use App\Repository\TerritorialCouncil\TerritorialCouncilRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Internal\Hydration\IterableResult;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Oneshot command, can be deleted after execution.
 */
class AddTerritorialCouncilMembershipCommand extends Command
{
    private const BATCH_SIZE = 1000;

    protected static $defaultName = 'app:territorial-council:add-membership';

    private $em;
    private $adherentRepository;
    private $territorialCouncilRepository;
    /** @var SymfonyStyle */
    private $io;

    public function __construct(
        EntityManagerInterface $em,
        TerritorialCouncilRepository $territorialCouncilRepository,
        AdherentRepository $adherentRepository
    ) {
        $this->em = $em;
        $this->adherentRepository = $adherentRepository;
        $this->territorialCouncilRepository = $territorialCouncilRepository;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Add Territorial Council member info to adherents.')
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->io->title('Starting adding Territorial Council member info to adherents.');

        // Referents
        $this->io->text('Referents:');
        $this->io->progressStart($this->getReferentCount());

        $count = 0;
        /** @var Adherent $referent */
        foreach ($this->getReferents() as $result) {
            /* @var Adherent $referent */
            $referent = $result[0];
            if ($referent->getManagedArea()->getTags()->count() > 1) {
                continue;
            }

            $this->addTerritorialCouncilMembership($referent, true);

            if (0 === (++$count % self::BATCH_SIZE)) {
                $this->em->clear();
            }

            $this->io->progressAdvance();
        }

        $this->io->progressFinish();

        // LRE
        $this->io->text('LRE:');
        $this->io->progressStart($this->getLreCount());

        $count = 0;
        /** @var Adherent $adherent */
        foreach ($this->getLre() as $result) {
            /* @var Adherent $adherent */
            $adherent = $result[0];
            $this->addTerritorialCouncilMembership($adherent, false);

            if (0 === (++$count % self::BATCH_SIZE)) {
                $this->em->clear();
            }

            $this->io->progressAdvance();
        }

        $this->io->progressFinish();

        $this->io->success('Territorial Council memberhip added successfully to adherents!');
    }

    private function getReferents(): IterableResult
    {
        return $this
            ->createReferentQueryBuilder()
            ->getQuery()
            ->iterate()
        ;
    }

    private function getReferentCount(): int
    {
        return $this
            ->createReferentQueryBuilder()
            ->select('COUNT(adherent)')
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    private function createReferentQueryBuilder(): QueryBuilder
    {
        return $this
            ->em
            ->getRepository(Adherent::class)
            ->createQueryBuilder('adherent')
            ->select('adherent', 'rma')
            ->join('adherent.managedArea', 'rma')
        ;
    }

    private function getLre(): IterableResult
    {
        return $this
            ->createLreQueryBuilder()
            ->getQuery()
            ->iterate()
        ;
    }

    private function getLreCount(): int
    {
        return $this
            ->createLreQueryBuilder()
            ->select('COUNT(adherent)')
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    private function createLreQueryBuilder(): QueryBuilder
    {
        return $this
            ->em
            ->getRepository(Adherent::class)
            ->createQueryBuilder('adherent')
            ->select('adherent', 'lre', 'tag')
            ->join('adherent.lreArea', 'lre')
            ->join('lre.referentTag', 'tag')
        ;
    }

    private function addTerritorialCouncilMembership(Adherent $adherent, bool $isReferent): void
    {
        if ($isReferent) {
            $qualityName = TerritorialCouncilQualityEnum::REFERENT;
            $zone = $adherent->getManagedArea()->getTags()->first();
        } else {
            $qualityName = TerritorialCouncilQualityEnum::LRE_MANAGER;
            $zone = $adherent->getLreArea()->getReferentTag();
        }
        $terco = $this->territorialCouncilRepository->findOneByReferentTag($zone);

        if (!$terco) {
            $this->io->warning(
                \sprintf(
                    'TerritorialCouncil with referent tag "%s" (code %s) has not been found (%s with email %s).',
                    $zone->getName(),
                    $zone->getCode(),
                    $isReferent ? 'referent' : 'adherent LRE',
                    $adherent->getEmailAddress()
                )
            );

            return;
        }

        if ($member = $this->adherentRepository->findByTerritorialCouncilAndQuality($terco, $qualityName, $adherent)) {
            $this->io->warning(
                \sprintf(
                    'Adherent (%s) cannot be added to TerritorialCouncil "%s" with quality "%s", because Adherent (%s) is already its member with this quality.',
                    $adherent->getEmailAddress(),
                    $terco->getNameCodes(),
                    $qualityName,
                    $member->getEmailAddress()
                )
            );

            return;
        }

        $quality = new TerritorialCouncilQuality($qualityName, $zone->getName());
        if ($adherent->hasTerritorialCouncilMembership()) {
            if ($adherent->getTerritorialCouncilMembership()->getTerritorialCouncil()->getId() === $terco->getId()) {
                $this->io->warning(\sprintf(
                    'Adherent ("%s") cannot be added as member of TerritorialCouncil "%s", because he is already its member',
                    $adherent->getEmailAddress(),
                    $terco->getNameCodes(),
                    $adherent->getTerritorialCouncilMembership()->getTerritorialCouncil()->getNameCodes()
                ));

                return;
            }

            $this->io->warning(\sprintf(
                'Adherent ("%s") cannot be added as member of TerritorialCouncil "%s", because he is already member of "%s".',
                $adherent->getEmailAddress(),
                $terco->getNameCodes(),
                $adherent->getTerritorialCouncilMembership()->getTerritorialCouncil()->getNameCodes()
            ));

            return;
        } else {
            $membership = new TerritorialCouncilMembership($terco);
            $membership->addQuality($quality);
            $membership->setAdherent($adherent);
            $adherent->setTerritorialCouncilMembership($membership);
            $this->em->persist($membership);
        }

        $this->em->flush();
    }
}
