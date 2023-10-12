<?php

namespace App\Command;

use App\Adherent\Tag\Command\RefreshAdherentTagCommand;
use App\Entity\Contribution\Payment;
use App\Ohme\ClientInterface;
use App\Repository\AdherentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsCommand(
    name: 'app:elected-representative:ohme-update',
    description: 'Update elected representatives from Ohme API',
)]
class ElectedRepresentativeOhmeUpdateCommand extends Command
{
    /** @var SymfonyStyle */
    private $io;

    public function __construct(
        private readonly ClientInterface $ohme,
        private readonly AdherentRepository $adherentRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly MessageBusInterface $bus,
    ) {
        parent::__construct();
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
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
                $this->io->progressAdvance();

                if (empty($contact['uuid_adherent'])) {
                    continue;
                }

                if (!$adherent = $this->adherentRepository->findByUuid($contact['uuid_adherent'])) {
                    $this->io->warning(sprintf('Adherent with uuid "%s" has not been found.', $contact['uuid_adherent']));

                    continue;
                }

                $payments = $this->ohme->getPayments(100, 0, ['contact_id' => $contact['id']]);

                foreach ($payments['data'] as $payment) {
                    if ($adherent->getPaymentByOhmeId($payment['id'])) {
                        continue;
                    }

                    $adherent->addPayment(Payment::fromArray($adherent, $payment));
                }

                $this->entityManager->flush();
                $this->bus->dispatch(new RefreshAdherentTagCommand($adherent->getUuid()));

                $this->pause();
            }

            $this->entityManager->clear();

            $offset += $limit;

            $this->pause();
        } while (0 !== $count && 0 !== $offset && $offset < $count);

        $this->io->progressFinish();

        return self::SUCCESS;
    }

    private function pause(): void
    {
        // Avoid OHME rate limit (100 requests / minute)
        usleep(700000);
    }
}
