<?php

namespace App\Command;

use App\Entity\ElectedRepresentative\Payment;
use App\Ohme\ClientInterface;
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
        private readonly ClientInterface $ohme,
        private readonly ElectedRepresentativeRepository $electedRepresentativeRepository,
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
                if (empty($contacts['count'])) {
                    $this->io->error('No contact found from Ohme API');
                }

                $count = $contacts['count'];

                $this->io->progressStart($count);
            }

            foreach ($contacts['data'] as $contact) {
                if (empty($contact['uuid_adherent'])) {
                    continue;
                }

                $electedRepresentative = $this->electedRepresentativeRepository->findOneByAdherentUuid($contact['uuid_adherent']);

                if (!$electedRepresentative) {
                    $this->io->warning(sprintf('ElectedRepresentative with uuid_adherent "%s" has not been found.', $contact['uuid_adherent']));

                    continue;
                }

                $payments = $this->ohme->getPayments(100, 0, [
                    'contact_id' => $contact['id'],
                ]);

                foreach ($payments['data'] as $payment) {
                    if ($electedRepresentative->getPaymentByOhmeId($payment['id'])) {
                        continue;
                    }

                    $electedRepresentative->addPayment(Payment::fromArray($electedRepresentative, $payment));
                }

                $this->entityManager->flush();

                $this->io->progressAdvance();
            }

            $this->entityManager->clear();

            $offset += $limit;
        } while (0 !== $count && 0 !== $offset && $offset < $count);

        $this->io->progressFinish();

        return 0;
    }
}
