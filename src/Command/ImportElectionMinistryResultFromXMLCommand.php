<?php

namespace App\Command;

use App\Election\ElectionManager;
use App\Election\VoteListNuanceEnum;
use App\Entity\Adherent;
use App\Entity\Election\MinistryListTotalResult;
use App\Repository\CityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sabre\Xml\Service;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ImportElectionMinistryResultFromXMLCommand extends Command
{
    private const INDEX_URL = 'https://elections.interieur.gouv.fr/telechargements/MUNICIPALES2020/resultatsT1/index.xml';
    private const CITY_INDEX_URL = 'https://elections.interieur.gouv.fr/telechargements/MUNICIPALES2020/resultatsT1/%s/%s000.xml';

    protected static $defaultName = 'app:election:import-ministry-results-from-xml';

    /** @var HttpClientInterface */
    private $httpClient;
    /** @var SymfonyStyle */
    private $io;
    /** @var CityRepository */
    private $cityRepository;
    /** @var ElectionManager */
    private $electionManager;
    /** @var EntityManagerInterface */
    private $entityManager;

    private $errors = [];
    private $author;

    /** @required */
    public function setCityRepository(CityRepository $cityRepository): void
    {
        $this->cityRepository = $cityRepository;
    }

    /** @required */
    public function setElectionManager(ElectionManager $electionManager): void
    {
        $this->electionManager = $electionManager;
    }

    /** @required */
    public function setEntityManager(EntityManagerInterface $entityManager): void
    {
        $this->entityManager = $entityManager;
    }

    protected function configure()
    {
        $this->addArgument('author-id', InputArgument::REQUIRED, 'Author id (adherent)');
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);
        $this->httpClient = HttpClient::create(['timeout' => 3600]);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->author = (int) $input->getArgument('author-id');
        $response = $this->httpClient->request('GET', self::INDEX_URL);

        $service = new Service();
        $dpts = $service->parse($response->getContent());

        $this->io->progressStart(\count($dpts[1]['value']));

        foreach ($dpts[1]['value'] as $dpt) {
            $code = $this->getValueData('CodDpt3Car', $dpt['value'])['value'];

            $isSuccess = false;
            while (false === $isSuccess) {
                try {
                    $response = $this->httpClient->request('GET', sprintf(self::CITY_INDEX_URL, $code, $code));
                    $response->getContent();
                    $isSuccess = true;
                } catch (\Exception $e) {
                    $this->io->warning($e->getMessage());
                }
            }

            $cities = $service->parse($response->getContent());

            foreach ($cities[1]['value'][4]['value'] as $cityAttr) {
                $this->updateResult($cities[1]['value'][0]['value'], $cityAttr);
            }
            $this->entityManager->flush();
            $this->entityManager->clear();

            $this->io->progressAdvance();
        }

        $this->io->progressFinish();

        $this->io->title('City errors');
        $this->io->table(['Errors'], array_map(function (string $error) { return [$error]; }, $this->errors));
    }

    private function updateResult(string $dptCode, array $cityAttr): void
    {
        $cityCode = $cityAttr['value'][0]['value'];
        $cityName = $cityAttr['value'][1]['value'];
        $inseeCode = $dptCode.$cityCode;

        if (false !== strpos($inseeCode, 'SR')) {
            if (false !== strpos($inseeCode, '69123SR')) {
                $inseeCode = 69380 + substr($inseeCode, -2);
            } elseif (false !== strpos($inseeCode, '75056SR')) {
                $inseeCode = 75100 + substr($inseeCode, -2);
            } elseif (false !== strpos($inseeCode, '13055SR')) {
                $inseeCode = 13200 + substr($inseeCode, -2);
            }
        }

        $city = $this->cityRepository->findByInseeCode($inseeCode);

        if (!$city) {
            $this->errors[] = 'City not found: '.$cityName.' ['.$inseeCode.']';

            return;
        }

        $voteResult = $this->electionManager->getMinistryVoteResultForCurrentElectionRound($city);

        if (!$voteResult) {
            throw new \RuntimeException('VoteResult not found');
        }

        foreach ($cityAttr['value'][9]['value'][0]['value'][6]['value'] as $data) {
            if ('{}Inscrits' === $data['name']) {
                $voteResult->setRegistered((int) $data['value'][0]['value']);
            }

            if ('{}Abstentions' === $data['name']) {
                $voteResult->setAbstentions((int) $data['value'][0]['value']);
            }

            if ('{}Exprimes' === $data['name']) {
                $voteResult->setExpressed((int) $data['value'][0]['value']);
            }

            if ('{}Votants' === $data['name']) {
                $voteResult->setParticipated((int) $data['value'][0]['value']);
            }
        }

        if ('{}Listes' === $cityAttr['value'][9]['value'][0]['value'][7]['name']) {
            foreach ($cityAttr['value'][9]['value'][0]['value'][7]['value'] as $list) {
                $listLabel = $this->getValueData('LibLisExt', $list['value'])['value'];
                $listNuance = $this->getValueData('CodNuaListe', $list['value'])['value'];
                $total = $this->getValueData('NbVoix', $list['value'])['value'];

                $listToUpdate = $voteResult->findListWithLabel($listLabel);
                if (!$listToUpdate) {
                    $listToUpdate = new MinistryListTotalResult();
                    $listToUpdate->setNuance(
                        \in_array($listNuance, VoteListNuanceEnum::getChoices(), true) ? $listNuance :
                            \in_array($tmp = ltrim($listNuance, 'L'), VoteListNuanceEnum::getChoices(), true) ? $tmp : null
                    );
                    $listToUpdate->setLabel($listLabel);
                    $voteResult->addListTotalResult($listToUpdate);
                }

                $listToUpdate->setTotal((int) $total);
            }
        } elseif ('{}CandidatsMaj' === $cityAttr['value'][9]['value'][0]['value'][7]['name']) {
            foreach ($this->getValueData('ListeCandidatsMaj', $cityAttr['value'][9]['value'][0]['value'][7]['value'])['value'] as $candidate) {
                $candidateFirstName = $this->getValueData('PrePsn', $candidate['value'])['value'];
                $candidateLastName = $this->getValueData('NomPsn', $candidate['value'])['value'];
                $listLabel = $candidateLastName.' '.$candidateFirstName;

                $total = $this->getValueData('NbVoix', $candidate['value'])['value'];

                $listToUpdate = $voteResult->findListWithLabel($listLabel);
                if (!$listToUpdate) {
                    $listToUpdate = new MinistryListTotalResult();
                    $listToUpdate->setLabel($listLabel);
                    $voteResult->addListTotalResult($listToUpdate);
                }

                $listToUpdate->setTotal((int) $total);
            }
        }

        if (!$voteResult->getId()) {
            $this->entityManager->persist($voteResult);
        }

        $voteResult->setUpdatedAt(new \DateTime());
        $voteResult->setUpdatedBy($this->entityManager->getReference(Adherent::class, $this->author));
    }

    private function getValueData(string $key, $rows): ?array
    {
        foreach ($rows as $row) {
            if ($row['name'] === '{}'.$key) {
                return $row;
            }
        }

        return null;
    }
}
