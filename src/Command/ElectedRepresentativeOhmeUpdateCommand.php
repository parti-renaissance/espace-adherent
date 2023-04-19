<?php

namespace App\Command;

use App\Ohme\Client;
use App\Ohme\ElectedRepresentativeManager;
use App\Repository\ElectedRepresentative\ElectedRepresentativeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ElectedRepresentativeOhmeUpdateCommand extends Command
{
    protected static $defaultName = 'app:elected-representative:ohme-update';

    /** @var SymfonyStyle */
    private $io;

    public function __construct(
        private readonly Client $ohme,
        private readonly ElectedRepresentativeRepository $electedRepresentativeRepository,
        private readonly ElectedRepresentativeManager $electedRepresentativeManager,
        private readonly EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Update elected representatives from Ohme API')
        ;
    }

    public function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $count = 0;
        $offset = 0;
        $limit = 100;

        do {
            $contacts = $this->ohme->getContacts($limit, $offset);

            if (0 === $count) {
                if (!\array_key_exists('count', $contacts) || !$contacts['count']) {
                    $this->io->error('No contact found from Ohme API');
                }

                $count = $contacts['count'];

                $this->io->progressStart($count);
            }

            foreach ($contacts['data'] as $contact) {
                if (!\array_key_exists('adherent_uuid', $contact) || !$contact['adherent_uuid']) {
                    continue;
                }

                $electedRepresentative = $this->electedRepresentativeRepository->findOneByAdherentUuid($contact['adherent_uuid']);

                if (!$electedRepresentative) {
                    $this->io->warning(sprintf('ElectedRepresentative with adherent_uuid "%s" has not been found.', $contact['adherent_uuid']));

                    continue;
                }

                $payments = $this->ohme->getPayments(100, 0, [
                    'contact_id' => $contact['id'],
                ]);

                foreach ($payments['data'] as $payment) {
                    $this->electedRepresentativeManager->synchronizePayments($electedRepresentative, $payment);
                }

                $this->io->progressAdvance();
            }

            $offset += $limit;
        } while (0 !== $count && 0 !== $offset && $offset < $count);

        $this->io->progressFinish();

        return 0;
    }
}
