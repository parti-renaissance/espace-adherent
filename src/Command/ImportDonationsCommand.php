<?php

namespace AppBundle\Command;

use AppBundle\Address\PostAddressFactory;
use AppBundle\Donation\DonatorManager;
use AppBundle\Donation\PayboxPaymentSubscription;
use AppBundle\Entity\Donation;
use AppBundle\Entity\Donator;
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
    private $em;

    private const TYPES_MAP = [
        'chèque' => Donation::TYPE_CHECK,
        'virement' => Donation::TYPE_TRANSFER,
    ];

    private const GENDERS_MAP = [
        'H' => Genders::MALE,
        'F' => Genders::FEMALE,
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
        'france' => 'FR',
        'grece' => 'FR',
        'hong kong' => 'HK',
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
        'royaume-uni' => 'GB',
        'singapour' => 'SG',
        'sri-lanka' => 'LK',
        'suisse' => 'CH',
        'thailande' => 'TH',
        'uae' => 'AE',
    ];

    /**
     * @var SymfonyStyle|null
     */
    private $io;

    public function __construct(
        FilesystemInterface $storage,
        PostAddressFactory $postAddressFactory,
        DonatorManager $donatorManager,
        EntityManagerInterface $em
    ) {
        $this->storage = $storage;
        $this->postAddressFactory = $postAddressFactory;
        $this->donatorManager = $donatorManager;
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
        $this->io->section('Starting import of Donation coordinates.');

        $csv = Reader::createFromStream($this->storage->readStream($input->getArgument('filename')));
        $csv->setHeaderOffset(0);

        $this->io->progressStart($total = $csv->count());

        $line = 0;
        foreach ($csv as $row) {
            $gender = $row['gender'];
            $firstName = $row['firstName'];
            $lastName = $row['lastName'];
            $email = $row['email'];
            $address = $row['address'];
            $postalCode = str_pad($row['postalCode'], 5, '0', \STR_PAD_LEFT);
            $cityName = $row['cityName'];
            $country = mb_strtolower(trim($row['country']));
            $nationality = mb_strtolower(trim($row['nationality']));
            $type = $row['type'];
            $amount = $row['amount'];
            $transferNumber = $row['transfer_number'];
            $checkNumber = $row['check_number'];
            $donatedAt = \DateTimeImmutable::createFromFormat('d/m/Y', $row['date']);

            if (!\array_key_exists($type, self::TYPES_MAP)) {
                $this->io->text("\"$type\" is not a valid transaction type.");

                continue;
            }

            if (!\array_key_exists($gender, self::GENDERS_MAP)) {
                $this->io->text("\"$gender\" is not a valid gender.");

                continue;
            }

            if (!\array_key_exists($country, self::COUNTRIES_MAP)) {
                $this->io->text("\"$country\" is not a valid country.");

                continue;
            }

            if (!\array_key_exists($nationality, self::COUNTRIES_MAP)) {
                $this->io->text("\"$nationality\" is not a valid nationality.");

                continue;
            }

            $donator = new Donator(
                $lastName,
                $firstName,
                $cityName,
                self::COUNTRIES_MAP[$country],
                $email,
                self::GENDERS_MAP[$gender],
                self::COUNTRIES_MAP[$nationality]
            );

            $donator->setIdentifier($this->donatorManager->incrementeIdentifier(false));

            $donation = new Donation(
                Uuid::uuid4(),
                self::TYPES_MAP[$type],
                $amount,
                $donatedAt,
                $this->postAddressFactory->createFlexible(
                    self::COUNTRIES_MAP[$country],
                    $postalCode,
                    $cityName,
                    $address
                ),
                null,
                PayboxPaymentSubscription::NONE,
                null,
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
            $this->em->flush();

            ++$line;

            $this->io->progressAdvance();

            if (0 === ($line % self::BATCH_SIZE)) {
                $this->em->clear();
            }
        }

        $this->em->flush();
        $this->em->clear();

        $this->io->progressFinish();

        $this->io->success("$total donations imported successfully !");
    }
}
