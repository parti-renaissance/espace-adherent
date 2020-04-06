<?php

namespace AppBundle\Command;

use AppBundle\Election\ElectionManager;
use AppBundle\Election\VoteListNuanceEnum;
use AppBundle\Entity\Election\VotePlaceResult;
use AppBundle\Entity\Election\VoteResultList;
use AppBundle\Entity\Election\VoteResultListCollection;
use AppBundle\Entity\VotePlace;
use AppBundle\Repository\CityRepository;
use AppBundle\Repository\VotePlaceRepository;
use Doctrine\Common\Persistence\ObjectManager;
use League\Csv\Reader;
use League\Flysystem\FilesystemInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ImportElectionVotePlaceResultsCommand extends Command
{
    protected static $defaultName = 'app:election:import-vote-place-results';

    /** @var FilesystemInterface */
    private $storage;
    /** @var ElectionManager */
    private $electionManager;
    /** @var VotePlaceRepository */
    private $votePlaceRepository;
    /** @var CityRepository */
    private $cityRepository;
    /** @var ObjectManager */
    private $entityManager;
    /** @var SymfonyStyle */
    private $io;
    private $errors = [];

    protected function configure()
    {
        $this->addArgument('file', InputArgument::REQUIRED, 'CSV file of results');
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $csv = Reader::createFromStream($this->storage->readStream($input->getArgument('file')));
        $csv->setDelimiter(';');

        $votePlaceDataLabels = [
            'dtp_code',
            'dtp_label',
            'city_code',
            'city_label',
            'vote_place_code',
            'Inscrits',
            'Abstentions',
            '% Abs/Ins',
            'Votants',
            '% Vot/Ins',
            'Blancs',
            '% Blancs/Ins',
            '% Blancs/Vot',
            'Nuls',
            '% Nuls/Ins',
            '% Nuls/Vot',
            'Exprimés',
            '% Exp/Ins',
            '% Exp/Vot',
        ];

        $votePlaceDataColumnNumber = \count($votePlaceDataLabels);

        $this->io->progressStart(\count($csv) - 1);

        foreach ($csv as $index => $row) {
            if (0 === $index) {
                continue;
            }

            $votePlaceData = array_combine($votePlaceDataLabels, \array_slice($row, 0, $votePlaceDataColumnNumber));

            $votePlaceCode = implode('_', [
                str_pad($votePlaceData['dtp_code'], 2, '0', \STR_PAD_LEFT).str_pad($votePlaceData['city_code'], 3, '0', \STR_PAD_LEFT),
                str_pad($votePlaceData['vote_place_code'], 4, '0', \STR_PAD_LEFT),
            ]);

            $votePlace = $this->votePlaceRepository->findOneBy(['code' => $votePlaceCode]);

            if (!$votePlace instanceof VotePlace) {
                $this->errors[] = 'VP not found: '.$votePlaceCode;
                continue;
            }

            $lists = $this->extractLists($row, $votePlaceDataColumnNumber);

            $this->updateVoteListCollection($votePlace, $lists);

            $voteResult = $this->getVoteResult($votePlace);

            $voteResult->setExpressed($votePlaceData['Exprimés']);
            $voteResult->setAbstentions($votePlaceData['Abstentions']);
            $voteResult->setRegistered($votePlaceData['Inscrits']);
            $voteResult->setParticipated($votePlaceData['Votants']);

            foreach ($lists as $listData) {
                foreach ($voteResult->getListTotalResults() as $list) {
                    if (0 === strcasecmp($list->getList()->getLabel(), $listData['list_label'])) {
                        $list->setTotal((int) $listData['voix']);
                        continue;
                    }
                }
            }

            $this->entityManager->flush();
            $this->entityManager->clear();

            $this->io->progressAdvance();
        }

        $this->io->progressFinish();

        $this->io->title('VotePlace errors');
        $this->io->table(['Errors'], array_map(function (string $error) { return [$error]; }, $this->errors));
    }

    private function getVoteResult(VotePlace $votePlace): VotePlaceResult
    {
        return $this->electionManager->getVotePlaceResultForCurrentElectionRound($votePlace, true);
    }

    private function extractLists(array $row, int $initialOffset): array
    {
        $listDataLabels = [
            'nb_pan',
            'nuance',
            'sexe',
            'nom',
            'prenom',
            'liste',
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

            if (empty($listData['nb_pan'])) {
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

    private function updateVoteListCollection(VotePlace $votePlace, array $lists): void
    {
        $listCollection = $this->electionManager->getListCollectionForVotePlace($votePlace);

        if (!$listCollection) {
            $city = $this->cityRepository->findByInseeCode($votePlace->getInseeCode());

            if (!$city) {
                return;
            }

            $listCollection = new VoteResultListCollection();
            $listCollection->setCity($city);

            $this->entityManager->persist($listCollection);
        }

        foreach ($lists as $newList) {
            if (!$listCollection->containsList($newList['list_label'])) {
                $list = new VoteResultList();
                $list->setLabel($newList['list_label']);

                if ($newList['nuance']) {
                    $list->setNuance(
                        \in_array($newList['nuance'], VoteListNuanceEnum::getChoices(), true) ?
                            $newList['nuance'] :
                            (\in_array(
                                $tmp = ltrim($newList['nuance'], 'L'),
                                VoteListNuanceEnum::getChoices(),
                                true
                            ) ? $tmp : null)
                    );
                }

                $list->setPosition($newList['nb_pan']);

                $listCollection->addList($list);
            }
        }

        $this->entityManager->flush();
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
    public function setVotePlaceRepository(VotePlaceRepository $votePlaceRepository): void
    {
        $this->votePlaceRepository = $votePlaceRepository;
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
}
