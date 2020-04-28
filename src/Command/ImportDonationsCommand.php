<?php

namespace AppBundle\Command;

use AppBundle\Address\PostAddressFactory;
use AppBundle\Donation\DonatorManager;
use AppBundle\Donation\PayboxPaymentSubscription;
use AppBundle\Entity\Donation;
use AppBundle\Entity\Donator;
use AppBundle\Repository\DonatorRepository;
use AppBundle\ValueObject\Genders;
use Doctrine\ORM\EntityManagerInterface;
use League\Csv\Reader;
use League\Flysystem\FilesystemInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ImportDonationsCommand extends Command
{
    protected static $defaultName = 'app:donations:import';

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
        'autriche' => 'AT',
        'belgique' => 'BE',
        'burkina faso' => 'BF',
        'canada' => 'CA',
        'chine' => 'CN',
        'danemark' => 'DK',
        'espagne' => 'ES',
        'etats-unis' => 'US',
        'etats unis' => 'US',
        'usa' => 'US',
        'france' => 'FR',
        'grece' => 'FR',
        'hong kong' => 'HK',
        'hong kong (chine)' => 'HK',
        'ile maurice' => 'MU',
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
        'monaco' => 'MC',
        'nicaragua' => 'NI',
        'panama' => 'PA',
        'pays bas' => 'NL',
        'philippines' => 'PH',
        'polynésie française' => 'PF',
        'portugal' => 'PT',
        'republique tcheque' => 'CZ',
        'rep. tcheque' => 'CZ',
        'royaume-uni' => 'GB',
        'royaume uni' => 'GB',
        'uk' => 'GB',
        'singapour' => 'SG',
        'sri-lanka' => 'LK',
        'suisse' => 'CH',
        'tahiti pf' => '',
        'thailande' => 'TH',
        'uae' => 'AE',
    ];

    private const NATIONALITY_MAP = [
        'francaise' => 'FR',
        'française' => 'FR',
        'belge' => 'BE',
    ];

    /**
     * @var SymfonyStyle|null
     */
    private $io;

    public function __construct(
        FilesystemInterface $storage,
        PostAddressFactory $postAddressFactory,
        DonatorManager $donatorManager,
        DonatorRepository $donatorRepository,
        EntityManagerInterface $em
    ) {
        $this->storage = $storage;
        $this->postAddressFactory = $postAddressFactory;
        $this->donatorManager = $donatorManager;
        $this->donatorRepository = $donatorRepository;
        $this->em = $em;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->addArgument('filename', InputArgument::REQUIRED)
            ->setDescription('Import Donations')
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $this->em->beginTransaction();

            $this->handleImport($input->getArgument('filename'));

            $this->em->commit();
        } catch (\Exception $exception) {
            $this->em->rollback();

            throw $exception;
        }
    }

    private function handleImport(string $filename): void
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
            $donatedAt = \DateTimeImmutable::createFromFormat('d/m/Y', trim($row['date']));

            if (!\array_key_exists($type, self::TYPES_MAP)) {
                $this->io->text("\"$type\" is not a valid transaction type. (line $line)");

                continue;
            }

            if (empty($email) || \in_array($email, self::NULL_VALUES, true)) {
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

            $amount = str_replace(',', '.', $amount);

            if (!is_numeric($amount)) {
                $this->io->text("\"$amount\" is not a valid amount. (line $line)");

                continue;
            }

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

            $this->em->persist($donator);
            $this->em->persist($donation);

            ++$count;

            $this->io->progressAdvance();

            if (0 === ($count % self::BATCH_SIZE)) {
                $this->em->flush();
                $this->em->clear();
            }
        }

        $this->updateDonatorIdentifier($identifier);

        $this->em->flush();
        $this->em->clear();

        $this->io->progressFinish();

        $this->io->success("$count donations imported successfully !");
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
        $this->donatorManager->updateIdentifier($identifier, false);
    }
}
