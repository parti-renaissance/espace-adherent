<?php

namespace App\Command;

use App\Adherent\UnregistrationHandler;
use App\Entity\Adherent;
use App\Membership\Event\UserEvent;
use App\Membership\UserEvents;
use App\Repository\AdherentRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use League\Csv\Reader;
use League\Flysystem\FilesystemInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class UnregisterAdherentsCommand extends Command
{
    protected static $defaultName = 'app:adherent:unregister';

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var AdherentRepository
     */
    private $adherentRepository;

    private $notFoundEmails = [];

    private $hosts = [];

    private $storage;

    private $dispatcher;

    private $handler;

    public function __construct(
        FilesystemInterface $storage,
        EntityManagerInterface $em,
        EventDispatcherInterface $eventDispatcher,
        UnregistrationHandler $handler
    ) {
        $this->storage = $storage;
        $this->em = $em;
        $this->handler = $handler;
        $this->dispatcher = $eventDispatcher;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->addArgument('fileUrl', InputArgument::REQUIRED)
            ->setDescription('Unregister adherents from CSV file (only the fifth column "email" is taken into account)')
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->adherentRepository = $this->em->getRepository(Adherent::class);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $emails = $this->parseCSV($input->getArgument('fileUrl'));

        $this->em->beginTransaction();

        $this->unregisterAdherents($emails, $output);

        $this->em->commit();

        $output->writeln(\PHP_EOL.'Unregistration finished.');

        if ($this->notFoundEmails) {
            $output->writeln('The following email addresses were not found in DB :');
            foreach ($this->notFoundEmails as $email) {
                $output->writeln($email);
            }
        }

        if ($this->hosts) {
            $output->writeln('The adherents with following email address are hosts of committees. They are not unregistered :');
            foreach ($this->hosts as $email) {
                $output->writeln($email);
            }
        }

        return 0;
    }

    private function parseCSV(string $filename): array
    {
        return array_column(
            iterator_to_array(
                Reader::createFromStream($this->storage->readStream($filename))->setHeaderOffset(0)
            ),
            'email'
        );
    }

    private function unregisterAdherents(array $emails, OutputInterface $output): void
    {
        $batchSize = 20;
        $i = 1;
        $progress = new ProgressBar($output, \count($emails));
        $progress->start();

        foreach ($emails as $email) {
            try {
                if (!$adherent = $this->adherentRepository->findOneByEmail($email)) {
                    $this->notFoundEmails[] = $email;

                    continue;
                }

                if ($adherent->isHost() || $adherent->isSupervisor()) {
                    $this->hosts[] = $email;

                    continue;
                }

                $this->handler->handle($adherent);

                $this->dispatcher->dispatch(new UserEvent($adherent), UserEvents::USER_DELETED);
            } catch (\Exception $ex) {
                throw new \RuntimeException(\PHP_EOL.sprintf('An error occured while unregistering an adherent with email "%s".', $email), 0, $ex);
            }

            if (0 === ($i % $batchSize)) {
                $progress->advance($batchSize);
                $this->em->clear(); // Detaches all objects from Doctrine for memory save
            }

            ++$i;
        }

        $this->em->clear();

        $progress->finish();
    }
}
