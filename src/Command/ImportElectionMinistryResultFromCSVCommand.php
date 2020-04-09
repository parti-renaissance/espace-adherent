<?php

namespace AppBundle\Command;

use AppBundle\Election\ElectionManager;
use AppBundle\Entity\City;
use AppBundle\Entity\Election\MinistryListTotalResult;
use AppBundle\Entity\Election\MinistryVoteResult;
use AppBundle\Repository\CityRepository;
use AppBundle\Repository\Election\MinistryVoteResultRepository;
use Doctrine\Common\Persistence\ObjectManager;
use League\Csv\Reader;
use League\Flysystem\FilesystemInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ImportElectionMinistryResultFromCSVCommand extends Command
{
    protected static $defaultName = 'app:election:import-ministry-results-from-csv';

    /** @var FilesystemInterface */
    private $storage;
    /** @var ElectionManager */
    private $electionManager;
    /** @var CityRepository */
    private $cityRepository;
    /** @var ObjectManager */
    private $entityManager;
    /** @var MinistryVoteResultRepository */
    private $ministryVoteResultRepository;
    /** @var SymfonyStyle */
    private $io;

    private $errors = [];

    protected function configure()
    {
        $this
            ->addArgument('file', InputArgument::REQUIRED, 'CSV file of results')
            ->addArgument('election-round-id', InputArgument::REQUIRED, 'ID of election round')
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $electionRound = $this->electionManager->findElectionRound($input->getArgument('election-round-id'));

        if (!$electionRound) {
            throw new \InvalidArgumentException('Election round not found');
        }

        $csv = Reader::createFromStream($this->storage->readStream($input->getArgument('file')));
        $csv->setDelimiter(';');

        $cityDataLabels = [
            'date_export',
            'dtp_code',
            'type_scrutin',
            'dtp_label',
            'city_code',
            'city_label',
            'Inscrits',
            'Abstentions',
            '% Abs/Ins',
            'Votants',
            '% Vot/Ins',
            'Blancs & Nuls',
            '% Blancs/Ins',
            '% Blancs/Vot',
            'Exprimés',
            '% Exp/Ins',
            '% Exp/Vot',
        ];

        $cityDataColumnNumber = \count($cityDataLabels);

        $this->io->progressStart(\count($csv) - 1);

        foreach ($csv as $index => $row) {
            if (0 === $index) {
                continue;
            }

            $cityData = array_combine($cityDataLabels, \array_slice($row, 0, $cityDataColumnNumber));

            $inseeCode = str_pad($cityData['dtp_code'], 2, '0', \STR_PAD_LEFT)
                .str_pad($cityData['city_code'], 3, '0', \STR_PAD_LEFT);

            $city = $this->cityRepository->findByInseeCode($inseeCode);

            if (!$city instanceof City) {
                $this->io->warning('City not found: '.$inseeCode.' '.$cityData['city_label']);
                $this->errors[] = 'City not found: '.$inseeCode;
                continue;
            }

            $lists = $this->extractLists($row, $cityDataColumnNumber);

            if (!$voteResult = $this->ministryVoteResultRepository->findOneForCity($city, $electionRound)) {
                $voteResult = new MinistryVoteResult($city, $electionRound);
            }

            $voteResult->setExpressed((int) $cityData['Exprimés']);
            $voteResult->setAbstentions((int) $cityData['Abstentions']);
            $voteResult->setRegistered((int) $cityData['Inscrits']);
            $voteResult->setParticipated((int) $cityData['Votants']);

            foreach ($lists as $listData) {
                if (!$listToUpdate = $voteResult->findListWithLabel($listData['list_label'])) {
                    $listToUpdate = new MinistryListTotalResult();
                    $listToUpdate->updateNuance($listData['nuance']);
                    $listToUpdate->setLabel($listData['list_label']);
                    $voteResult->addListTotalResult($listToUpdate);
                }

                $listToUpdate->setTotal((int) $listData['voix']);
            }

            if (!$voteResult->getId()) {
                $this->entityManager->persist($voteResult);
            }

            if (0 === $index % 500) {
                $this->entityManager->flush();

                $this->entityManager->clear(MinistryVoteResult::class);
                $this->entityManager->clear(MinistryListTotalResult::class);
                $this->entityManager->clear(City::class);
            }

            $this->io->progressAdvance();
        }

        $this->entityManager->flush();

        $this->io->progressFinish();

        $this->io->title('VotePlace errors');
        $this->io->table(['Errors'], array_map(function (string $error) { return [$error]; }, $this->errors));
    }

    private function extractLists(array $row, int $initialOffset): array
    {
        $listDataLabels = [
            'nuance',
            'sexe',
            'nom',
            'prenom',
            'liste',
            'Sièges / Elu',
            'Sièges Secteur',
            'Sièges CC',
            'voix',
            '% Voix/Ins',
            '% Voix/Exp',
        ];
        $listDataColumnNumber = \count($listDataLabels);
        $offset = $initialOffset;

        $lists = [];

        while ($listDataColumnNumber === \count($listData = \array_slice($row, $offset, $listDataColumnNumber))) {
            $listData = array_combine($listDataLabels, $listData);
            $offset += $listDataColumnNumber;

            if (empty($listData['voix'])) {
                continue;
            }

            $listData['list_label'] = mb_substr(
                $listData['liste'] ? $listData['liste'] : $listData['nom'].' '.$listData['prenom'],
                0,
                255
            );

            $lists[] = $listData;
        }

        return $lists;
    }

    /** @required */
    public function setStorage(FilesystemInterface $storage): void
    {
        $this->storage = $storage;
    }

    /** @required */
    public function setElectionManager(ElectionManager $electionManager): void
    {
        $this->electionManager = $electionManager;
    }

    /** @required */
    public function setCityRepository(CityRepository $cityRepository): void
    {
        $this->cityRepository = $cityRepository;
    }

    /** @required */
    public function setEntityManager(ObjectManager $entityManager): void
    {
        $this->entityManager = $entityManager;
    }

    /** @required */
    public function setMinistryVoteResultRepository(MinistryVoteResultRepository $ministryVoteResultRepository): void
    {
        $this->ministryVoteResultRepository = $ministryVoteResultRepository;
    }
}
