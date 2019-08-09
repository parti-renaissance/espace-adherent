<?php

namespace AppBundle\Command;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\Unregistration;
use AppBundle\Membership\AdherentRegistry;
use AppBundle\Membership\UserEvent;
use AppBundle\Membership\UserEvents;
use AppBundle\Repository\AdherentRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use League\Csv\Reader;
use League\Flysystem\FilesystemInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

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

    /**
     * @var AdherentRegistry
     */
    private $adherentRegistry;

    private $notFoundEmails = [];

    private $hosts = [];

    private $citizenProjectAdministrators = [];

    private $storage;

    private $dispatcher;

    public function __construct(
        FilesystemInterface $storage,
        EntityManagerInterface $em,
        AdherentRegistry $adherentRegistry,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->storage = $storage;
        $this->em = $em;
        $this->adherentRegistry = $adherentRegistry;
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

        if ($this->citizenProjectAdministrators) {
            $output->writeln('The adherents with following email address are administrators of citizen projects. They are not unregistered :');
            foreach ($this->citizenProjectAdministrators as $email) {
                $output->writeln($email);
            }
        }
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

                if ($adherent->isHost()) {
                    $this->hosts[] = $email;

                    continue;
                }

                if ($adherent->isCitizenProjectAdministrator()) {
                    $this->citizenProjectAdministrators[] = $email;

                    continue;
                }

                $this->adherentRegistry->unregister($adherent, Unregistration::createFromAdherent($adherent));

                $this->dispatcher->dispatch(UserEvents::USER_DELETED, new UserEvent($adherent));
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
