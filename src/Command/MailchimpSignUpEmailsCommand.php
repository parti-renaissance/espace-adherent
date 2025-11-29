<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\SubscriptionType;
use App\Mailchimp\SignUp\SignUpHandler;
use App\Mailchimp\Synchronisation\Command\AdherentChangeCommand;
use App\Repository\AdherentRepository;
use Doctrine\ORM\EntityManagerInterface as ObjectManager;
use League\Csv\Reader;
use League\Flysystem\FilesystemOperator;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsCommand(
    name: 'mailchimp:signup:emails',
    description: 'Send SignUp form for each contact from CSV file',
)]
class MailchimpSignUpEmailsCommand extends Command
{
    private $repository;
    private $bus;
    private $signUpHandler;
    private $storage;
    private $em;
    /** @var SymfonyStyle */
    private $io;

    public function __construct(
        AdherentRepository $repository,
        MessageBusInterface $bus,
        SignUpHandler $signUpHandler,
        FilesystemOperator $defaultStorage,
        ObjectManager $em,
    ) {
        $this->repository = $repository;
        $this->bus = $bus;
        $this->signUpHandler = $signUpHandler;
        $this->storage = $defaultStorage;
        $this->em = $em;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('file', InputArgument::REQUIRED, 'CSV file with emails on first column')
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $file = $input->getArgument('file');

        if (!$this->storage->has($file)) {
            throw new \RuntimeException('File does not exist');
        }

        $csv = Reader::createFromStream($this->storage->readStream($file));
        $csv->setHeaderOffset(0);
        $total = $csv->count();

        if (!$this->io->confirm(\sprintf('Are you sure to subscribe %d contacts?', $total))) {
            return self::FAILURE;
        }

        $this->io->progressStart($total);

        $emailSubscribed = [];
        $emailError = [];
        $errors = [];

        $subscriptionTypes = $this->em->getRepository(SubscriptionType::class)->findAll();

        foreach ($csv as $row) {
            $this->io->progressAdvance();

            if (!$adherent = $this->repository->findOneByEmail($email = $row['email'])) {
                $errors[] = [
                    'email' => $email,
                    'message' => 'Adherent not found',
                ];

                continue;
            }

            $codes = $adherent->getSubscriptionTypeCodes();
            foreach ($subscriptionTypes as $type) {
                if (!\in_array($type->getCode(), $codes, true)) {
                    $adherent->addSubscriptionType($type);
                }
            }

            if ($this->signUpHandler->signUpAdherent($adherent)) {
                $adherent->setEmailUnsubscribed(false);
                $this->em->flush();

                $this->bus->dispatch(new AdherentChangeCommand($adherent->getUuid(), $adherent->getEmailAddress()));

                $emailSubscribed[] = $email;
            } else {
                $emailError[] = $email;
            }
        }

        $this->io->progressFinish();

        $this->io->title('Report:');

        $this->io->table([], [
            ['Adherent subscribed', \count($emailSubscribed)],
            ['Adherent not found', \count($errors)],
            ['Error', \count($emailError)],
            ['Total', $total],
        ]);

        if ($emailError) {
            $this->io->table(
                ['email in error'],
                array_map(static function (string $email) {return (array) $email; }, $emailError)
            );
        }

        return self::SUCCESS;
    }
}
