<?php

namespace AppBundle\Command;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\Unregistration;
use AppBundle\Membership\AdherentRegistry;
use AppBundle\Repository\AdherentRepository;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;

class UnregisterAdherentsCommand extends ContainerAwareCommand
{
    const COMMAND_NAME = 'app:adherent:unregister';

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

    protected function configure()
    {
        $this
            ->setName(self::COMMAND_NAME)
            ->addArgument('fileUrl', InputArgument::REQUIRED)
            ->setDescription('Unregister adherents from CSV file (only the fifth column "email" is taken into account)');
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $this->adherentRepository = $this->em->getRepository(Adherent::class);
        $this->adherentRegistry = $this->getContainer()->get(AdherentRegistry::class);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $emails = $this->parseCSV($input->getArgument('fileUrl'), 'r');
        } catch (FileNotFoundException $exception) {
            $output->writeln($exception->getMessage());

            return 1;
        }

        $this->em->beginTransaction();

        $this->unregisterAdherents($emails, $output);

        $this->em->commit();

        $output->writeln(PHP_EOL.'Unregistration finished.');

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
        if (false === ($handle = fopen($filename, 'r'))) {
            throw new FileNotFoundException(sprintf('% not found', $filename));
        }

        $isFirstRow = true;
        $emails = [];
        while (false !== ($data = fgetcsv($handle, 100000, ';'))) {
            if (true === $isFirstRow) {
                $isFirstRow = false;

                continue;
            }

            $row = array_map('trim', $data);
            $emails[] = $row[4];
        }
        fclose($handle);

        return $emails;
    }

    private function unregisterAdherents(array $emails, OutputInterface $output): void
    {
        $batchSize = 20;
        $i = 1;
        $progress = new ProgressBar($output, count($emails));
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

                if ($adherent->isProjectAdministrator()) {
                    $this->citizenProjectAdministrators[] = $email;

                    continue;
                }

                $unregistration = Unregistration::createFromAdherent($adherent);
                $this->adherentRegistry->unregister($adherent, $unregistration);
            } catch (\Exception $ex) {
                throw new \RuntimeException(PHP_EOL.sprintf('An error occured while unregistering an adherent with email "%s".', $email), 0, $ex);
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
