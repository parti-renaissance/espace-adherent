<?php

declare(strict_types=1);

namespace App\Command\Geo;

use App\Command\Geo\Helper\Persister;
use App\Command\Geo\Helper\Summary;
use App\Entity\Geo\City;
use App\Entity\Geo\Department;
use App\Entity\Geo\District;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsCommand(
    name: 'app:geo:update-districts',
    description: 'Update electoral district according to data.gouv.fr',
)]
final class UpdateDistrictsCommand extends Command
{
    private const SOURCE = 'https://www.data.gouv.fr/en/datasets/r/4d0b70e1-7757-43cc-882b-5c3b04fe38b4';

    private const IGNORED_DEPARTMENTS = [
        'ZZ',
    ];

    private const DEPARTMENT_CODE = [
        'ZA' => '971',
        'ZB' => '972',
        'ZC' => '973',
        'ZD' => '974',
        'ZM' => '976',
        'ZN' => '988',
        'ZP' => '987',
        'ZS' => '975',
        'ZW' => '986',
    ];

    private const DEPARTMENT_CODE_BY_CITY = [
        'ZX-701' => '977', // Saint-Barthélémy
        'ZX-801' => '978', // Saint-Martin
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
     * @var array<string, Department>
     */
    private $departments;

    /**
     * @var array<string, City>
     */
    private $cities;

    public function __construct(HttpClientInterface $httpClient, EntityManagerInterface $em)
    {
        $this->httpClient = $httpClient;
        $this->em = $em;

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
        $this->departments = $this->em->getRepository(Department::class)->findAllGroupedByCode();

        $this->cities = $this->em->getRepository(City::class)->findAllGroupedByCode();
        foreach ($this->cities as $city) {
            /* @var City $city */
            $city->clearDistricts();
        }

        $districts = $this->em->getRepository(District::class)->findAll();
        foreach ($districts as $district) {
            $district->activate(false);
            $district->clearCities();
            $this->entities->set($district->getCode(), $district);
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io->title('Start updating districts');

        $source = $this->loadSource();

        $header = array_shift($source);
        foreach ($source as $raw) {
            $row = array_combine($header, $raw);
            $this->readRow($row);
        }

        $summary = new Summary($this->io);
        $summary->run($this->entities);

        $persister = new Persister($this->io, $this->em);
        $persister->run($this->entities, $input->getOption('dry-run'));

        return self::SUCCESS;
    }

    private function readRow(array $row): void
    {
        $codeDepartment = str_pad($row['CODE DPT'], 2, '0', \STR_PAD_LEFT);
        if (\in_array($codeDepartment, self::IGNORED_DEPARTMENTS, true)) {
            return;
        }

        $codeCityPartial = str_pad($row['CODE COMMUNE'], 3, '0', \STR_PAD_LEFT);
        $specialDepartment = \sprintf('%s-%s', $codeDepartment, $codeCityPartial);
        $codeDepartment = self::DEPARTMENT_CODE_BY_CITY[$specialDepartment]
            ?? self::DEPARTMENT_CODE[$codeDepartment]
            ?? $codeDepartment;

        $codeCity = \sprintf('%s%s', substr($codeDepartment, 0, 2), $codeCityPartial);

        $number = $row['CODE CIRC LEGISLATIVE'];
        $code = \sprintf('%s-%d', $codeDepartment, $row['CODE CIRC LEGISLATIVE']);
        $name = \sprintf('%s (%d)', $row['NOM DPT'], $number);

        /* @var Department $department */
        $department = $this->departments[$codeDepartment] ?? null;
        if (!$department) {
            throw new \RuntimeException(\sprintf('Department %s not found', $codeDepartment));
        }

        $district = $this->entities->get($code);
        if (!$district) {
            $district = new District($code, $name, $number, $department);
            $this->entities->set($code, $district);
        }
        $district->activate();
        $district->setName($name);

        /* @var City $city */
        $city = $this->cities[$codeCity] ?? null;
        if ($city) {
            $district->addCity($city);
        }
    }

    private function loadSource(): array
    {
        $this->io->section('Loading districts from source');

        $filename = \sprintf('%s/%s', sys_get_temp_dir(), uniqid(md5(self::SOURCE), true));
        $this->io->comment([
            \sprintf('Fetching data from %s', self::SOURCE),
            \sprintf('Writing to %s', $filename),
        ]);

        $response = $this->httpClient->request('GET', self::SOURCE);
        file_put_contents($filename, $response->getContent());

        $reader = new Xlsx();
        $spreadsheet = $reader->load($filename);

        return $spreadsheet->getActiveSheet()->toArray();
    }
}
