<?php

namespace AppBundle\Command;

use AppBundle\Entity\Adherent;
use AppBundle\Mailchimp\Synchronisation\Command\AdherentChangeChangeCommand;
use AppBundle\Repository\AdherentRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;

class MailchimpSyncAllAdherentsCommand extends Command
{
    protected static $defaultName = 'mailchimp:sync:all-adherents';

    private $adherentRepository;
    private $entityManager;
    private $bus;
    /** @var SymfonyStyle */
    private $io;

    public function __construct(AdherentRepository $adherentRepository, ObjectManager $entityManager, MessageBusInterface $bus)
    {
        $this->adherentRepository = $adherentRepository;
        $this->entityManager = $entityManager;
        $this->bus = $bus;

        parent::__construct();
    }

    protected function configure()
    {
        $this->addOption('limit', null, InputOption::VALUE_REQUIRED, null, 0);
    }

    public function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $count = $this->adherentRepository->countActiveAdherents();
        $limit = (int) $input->getOption('limit');

        $this->io->progressStart($limit && $limit < $count ? $limit : $count);
        $offset = 0;

        /** @var Adherent[] $result */
        while ($result = $this->getAdherents($limit, $offset)) {
            foreach ($result as $index => $adherent) {
                $this->bus->dispatch(new AdherentChangeChangeCommand(
                    $adherent->getUuid(),
                    $adherent->getEmailAddress()
                ));
                $this->io->progressAdvance();

                if ($limit && $limit <= $offset + $index + 1) {
                    break;
                }
            }

            $this->entityManager->clear();

            $offset += \count($result);
        }

        $this->io->progressFinish();
    }

    private function getAdherents(int $limit, int $offset): array
    {
        return $this->adherentRepository->findBy(
            ['status' => Adherent::ENABLED],
            null,
            $limit && $limit < 5 ? $limit : 5,
            $offset
        );
    }
}
