<?php

namespace AppBundle\Command;

use AppBundle\Entity\SubscriptionType;
use AppBundle\Mailchimp\SignUp\SignUpHandler;
use AppBundle\Mailchimp\Synchronisation\Command\AdherentChangeCommand;
use AppBundle\Repository\AdherentRepository;
use Doctrine\Common\Persistence\ObjectManager;
use League\Csv\Reader;
use League\Flysystem\Filesystem;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;

class MailchimpSignUpEmailsCommand extends Command
{
    protected static $defaultName = 'mailchimp:signup:emails';

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
        Filesystem $storage,
        ObjectManager $em
    ) {
        $this->repository = $repository;
        $this->bus = $bus;
        $this->signUpHandler = $signUpHandler;
        $this->storage = $storage;
        $this->em = $em;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Send SignUp form for each contact from CSV file')
            ->addArgument('file', InputArgument::REQUIRED, 'CSV file with emails on first column')
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $file = $input->getArgument('file');

        if (!$this->storage->has($file)) {
            throw new \RuntimeException('File does not exist');
        }

        $csv = Reader::createFromStream($this->storage->readStream($file));
        $csv->setHeaderOffset(0);
        $total = $csv->count();

        if (!$this->io->confirm(sprintf('Are you sure to subscribe %d contacts?', $total))) {
            return 1;
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

            $adherent->setSubscriptionTypes($subscriptionTypes);

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
    }
}
