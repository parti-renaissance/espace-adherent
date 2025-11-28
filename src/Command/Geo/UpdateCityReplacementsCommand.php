<?php

declare(strict_types=1);

namespace App\Command\Geo;

use App\Command\Geo\Helper\Persister;
use App\Command\Geo\Helper\Summary;
use App\Entity\Geo\City;
use App\Repository\Geo\CityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use PhpOffice\PhpSpreadsheet\Reader\Xls;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsCommand(
    name: 'app:geo:update-city-replacements',
    description: 'Update cities replacements in France according to INSEE',
)]
final class UpdateCityReplacementsCommand extends Command
{
    private const SOURCES = [
        'https://www.insee.fr/fr/statistiques/fichier/2549968/communes_nouvelles_2015.xls',
        'https://www.insee.fr/fr/statistiques/fichier/2549968/communes_nouvelles_2016.xls',
        'https://www.insee.fr/fr/statistiques/fichier/2549968/communes_nouvelles_2017.xls',
        'https://www.insee.fr/fr/statistiques/fichier/2549968/communes_nouvelles_2018.xls',
        'https://www.insee.fr/fr/statistiques/fichier/2549968/communes_nouvelles_2019.xls',
    ];

    /**
     * @var HttpClientInterface
     */
    private $httpClient;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var SymfonyStyle
     */
    private $io;

    /**
     * @var Collection
     */
    private $entities;

    /**
     * @var CityRepository
     */
    private $cityRepository;

    public function __construct(
        HttpClientInterface $httpClient,
        EntityManagerInterface $em,
        CityRepository $cityRepository,
    ) {
        $this->httpClient = $httpClient;
        $this->em = $em;
        $this->cityRepository = $cityRepository;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Execute the algorithm but will not persist in database.')
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->io = new SymfonyStyle($input, $output);
        $this->entities = new ArrayCollection();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io->title('Start updating city replacements');

        foreach (self::SOURCES as $source) {
            $rows = $this->loadSource($source);
            $this->io->progressStart(\count($rows));
            foreach ($rows as $row) {
                $this->readRow($row);
                $this->io->progressAdvance();
            }
            $this->io->progressFinish();
        }

        $summary = new Summary($this->io);
        $summary->run($this->entities);

        $persister = new Persister($this->io, $this->em);
        $persister->run($this->entities, $input->getOption('dry-run'));

        return self::SUCCESS;
    }

    private function readRow(array $row): void
    {
        $oldCode = (string) $row['DepComA'];
        $newCode = (string) $row['DepComN'];

        // Skip name changing
        if ($oldCode === $newCode) {
            return;
        }

        $oldName = $row['NomCA'];
        $old = $this->entities->get($oldCode)
            ?: $this->cityRepository->findOneBy(['code' => $oldCode])
            ?: new City($oldCode, $oldName);

        $newName = $row['NomCN'];
        $new = $this->entities->get($newCode)
            ?: $this->cityRepository->findOneBy(['code' => $newCode])
            ?: new City($newCode, $newName);

        $old->activate(false);
        $old->setName($oldName);
        $old->setReplacement($new);

        $this->entities->set($oldCode, $old);
        $this->entities->set($newCode, $new);
    }

    private function loadSource(string $source): array
    {
        $this->io->section('Loading replacements from source');

        $filename = \sprintf('%s/%s', sys_get_temp_dir(), uniqid(md5($source), true));
        $this->io->comment([
            \sprintf('Fetching data from %s', $source),
            \sprintf('Writing to %s', $filename),
        ]);

        $response = $this->httpClient->request('GET', $source);
        file_put_contents($filename, $response->getContent());

        $reader = new Xls();
        $spreadsheet = $reader->load($filename);

        $rows = $spreadsheet->getSheet(0)->toArray();

        $header = array_shift($rows);
        array_walk($rows, static function (&$row) use ($header) {
            $row = array_combine($header, $row);
        });

        return $rows;
    }
}
