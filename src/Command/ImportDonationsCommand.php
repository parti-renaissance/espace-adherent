<?php

declare(strict_types=1);

namespace App\Command;

use App\Address\PostAddressFactory;
use App\Donation\DonatorManager;
use App\Donation\Paybox\PayboxPaymentSubscription;
use App\Entity\Donation;
use App\Entity\Donator;
use App\Repository\DonatorRepository;
use App\ValueObject\Genders;
use Doctrine\ORM\EntityManagerInterface;
use League\Csv\Reader;
use League\Flysystem\FilesystemOperator;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:donations:import',
)]
class ImportDonationsCommand extends Command
{
    private const BATCH_SIZE = 250;

    private $storage;
    private $postAddressFactory;
    private $donatorManager;
    private $donatorRepository;
    private $em;

    private const TYPES_MAP = [
        'chèque' => Donation::TYPE_CHECK,
        'virement' => Donation::TYPE_TRANSFER,
    ];

    private const GENDERS_MAP = [
        'H' => Genders::MALE,
        'F' => Genders::FEMALE,
    ];

    private const NULL_VALUES = [
        'NON',
    ];

    private const COUNTRIES_MAP = [
        'afrique du sud' => 'ZA',
        'albanie' => 'AL',
        'allegmagne' => 'DE',
        'allemagne' => 'DE',
        'argentina' => 'AR',
        'australie' => 'AU',
        'autriche' => 'AT',
        'belgique' => 'BE',
        'burkina faso' => 'BF',
        'cameroun' => 'CM',
        'canada' => 'CA',
        'chine' => 'CN',
        'danemark' => 'DK',
        'espagne' => 'ES',
        'etats-unis' => 'US',
        'etats unis' => 'US',
        'usa' => 'US',
        'france' => 'FR',
        'grece' => 'GR',
        'hong kong' => 'HK',
        'hong kong (chine)' => 'HK',
        'ile maurice' => 'MU',
        'irlande' => 'IE',
        'israel' => 'IL',
        'italie' => 'IT',
        'jordanie' => 'JO',
        'liban' => 'LB',
        'lituanie' => 'LT',
        'luxembourg' => 'LU',
        'madagascar' => 'MG',
        'malaisie' => 'MY',
        'malte' => 'MT',
        'maroc' => 'MA',
        'martinique' => 'MQ',
        'monaco' => 'MC',
        'nicaragua' => 'NI',
        'nouvelle caledonie' => 'NC',
        'panama' => 'PA',
        'pays bas' => 'NL',
        'philippines' => 'PH',
        'polynésie française' => 'PF',
        'portugal' => 'PT',
        'republique tcheque' => 'CZ',
        'rep. tcheque' => 'CZ',
        'republique-tcheque' => 'CZ',
        'royaume-uni' => 'GB',
        'royaume uni' => 'GB',
        'uk' => 'GB',
        'singapour' => 'SG',
        'sri-lanka' => 'LK',
        'suisse' => 'CH',
        'tahiti pf' => '',
        'thailande' => 'TH',
        'uae' => 'AE',
        'emirats arabes unis' => 'AE',
    ];

    private const NATIONALITY_MAP = [
        'algerienne' => 'DZ',
        'francaise' => 'FR',
        'française' => 'FR',
        'belge' => 'BE',
        'americaine' => 'US',
        'canadienne' => 'CA',
        'allemande' => 'DE',
        'irlandaise' => 'IE',
        'marocaine' => 'MA',
        'neerlandaise' => 'NL',
        'suisse' => 'CH',
        'angolaise' => 'AO',
        'australienne' => 'AU',
        'britannique' => 'UK',
        'camerounaise' => 'CM',
        'congolaise' => 'CD',
        'italienne' => 'IT',
        'togolaise' => 'TG',
        'tunisienne' => 'TN',
    ];

    /**
     * @var SymfonyStyle|null
     */
    private $io;

    public function __construct(
        FilesystemOperator $defaultStorage,
        PostAddressFactory $postAddressFactory,
        DonatorManager $donatorManager,
        DonatorRepository $donatorRepository,
        EntityManagerInterface $em,
    ) {
        $this->storage = $defaultStorage;
        $this->postAddressFactory = $postAddressFactory;
        $this->donatorManager = $donatorManager;
        $this->donatorRepository = $donatorRepository;
        $this->em = $em;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('filename', InputArgument::REQUIRED)
            ->addOption('dry-run', null, InputOption::VALUE_NONE)
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $dryRun = (bool) $input->getOption('dry-run');

        try {
            $this->em->beginTransaction();

            $this->handleImport($input->getArgument('filename'), $dryRun);

            $this->em->commit();
        } catch (\Exception $exception) {
            $this->em->rollback();

            throw $exception;
        }

        return self::SUCCESS;
    }

    private function handleImport(string $filename, bool $dryRun = false): void
    {
        $this->io->section('Starting import of Donation.');

        $csv = Reader::createFromStream($this->storage->readStream($filename));
        $csv->setHeaderOffset(0);

        $this->io->progressStart($total = $csv->count());

        $identifier = $this->getLastDonatorIdentifier();

        $line = 1;
        $count = 0;
        foreach ($csv as $row) {
            ++$line;

            $gender = trim($row['gender']);
            $firstName = trim($row['firstName']);
            $lastName = trim($row['lastName']);
            $email = trim($row['email']);
            $address = trim($row['address']);
            $postalCode = str_pad(trim($row['postalCode']), 5, '0', \STR_PAD_LEFT);
            $cityName = trim($row['cityName']);
            $country = mb_strtolower(trim($row['country']));
            $nationality = mb_strtolower(trim($row['nationality']));
            $type = mb_strtolower($row['type']);
            $amount = trim($row['amount']);
            $transferNumber = trim($row['transfer_number']);
            $checkNumber = trim($row['check_number']);
            $donatedAt = \DateTimeImmutable::createFromFormat('Y-m-d', trim($row['date']));
            $beneficiary = trim($row['beneficiary']);

            if (!\array_key_exists($type, self::TYPES_MAP)) {
                $this->io->text("\"$type\" is not a valid transaction type. (line $line)");

                continue;
            }

            if (empty($email) || \in_array($email, self::NULL_VALUES, true)) {
                $email = null;
            }

            if ($email && false === filter_var($email, \FILTER_VALIDATE_EMAIL)) {
                $this->io->text("\"$email\" is not a valid email address. (line $line)");

                $email = null;
            }

            if (empty($gender)) {
                $gender = null;
            }

            if ($gender && !\array_key_exists($gender, self::GENDERS_MAP)) {
                $this->io->text("\"$gender\" is not a valid gender. (line $line)");

                continue;
            }

            if (!\array_key_exists($country, self::COUNTRIES_MAP)) {
                $this->io->text("\"$country\" is not a valid country. (line $line)");

                continue;
            }

            if (empty($nationality)) {
                $nationality = null;
            }

            if ($nationality && !\array_key_exists($nationality, self::NATIONALITY_MAP)) {
                $this->io->text("\"$nationality\" is not a valid nationality. (line $line)");

                continue;
            }

            $amount = str_replace(["\u{a0}", ' ', ','], ['', '', '.'], $amount);

            if (!is_numeric($amount)) {
                $this->io->text("\"$amount\" is not a valid amount. (line $line)");

                continue;
            }

            if (!$donator = $this->findDonator($firstName, $lastName, $email)) {
                $identifier = $this->incrementDonatorIdentifier($identifier);

                $donator = new Donator(
                    $lastName,
                    $firstName,
                    $cityName,
                    self::COUNTRIES_MAP[$country],
                    $email,
                    $gender ? self::GENDERS_MAP[$gender] : null
                );

                $donator->setIdentifier($identifier);
            }

            $donation = new Donation(
                Uuid::uuid4(),
                self::TYPES_MAP[$type],
                $amount * 100,
                $donatedAt,
                $this->postAddressFactory->createFlexible(
                    $country ? self::COUNTRIES_MAP[$country] : null,
                    $postalCode,
                    $cityName,
                    $address
                ),
                null,
                PayboxPaymentSubscription::NONE,
                null,
                $nationality ? self::NATIONALITY_MAP[$nationality] : null,
                null,
                $donator
            );

            if (Donation::TYPE_TRANSFER === self::TYPES_MAP[$type] && !empty($transferNumber)) {
                $donation->setTransferNumber($transferNumber);
            }

            if (Donation::TYPE_CHECK === self::TYPES_MAP[$type] && !empty($checkNumber)) {
                $donation->setCheckNumber($checkNumber);
            }

            if (!empty($beneficiary)) {
                $donation->setBeneficiary($beneficiary);
            }

            if (!$dryRun) {
                $this->em->persist($donator);
                $this->em->persist($donation);
                $this->em->flush();
            }

            ++$count;

            $this->io->progressAdvance();

            if (!$dryRun && 0 === ($count % self::BATCH_SIZE)) {
                $this->em->clear();
            }
        }

        if (!$dryRun) {
            $this->updateDonatorIdentifier($identifier);

            $this->em->clear();
        }

        $this->io->progressFinish();

        $this->io->success("$count donations imported successfully !");
    }

    private function findDonator(string $firstName, string $lastName, ?string $email = null): ?Donator
    {
        return !empty($email) ? $this->donatorRepository->findOneForMatching($email, $firstName, $lastName) : null;
    }

    private function getLastDonatorIdentifier(): string
    {
        return $this->donatorManager->findLastIdentifier()->getIdentifier();
    }

    private function incrementDonatorIdentifier(string $identifier): string
    {
        return $this->donatorManager->getNextAccountId($identifier);
    }

    private function updateDonatorIdentifier(string $identifier): void
    {
        $this->donatorManager->updateIdentifier($identifier, true);
    }
}
