<?php

namespace App\NationalEvent\Handler;

use App\Entity\NationalEvent\EventInscription;
use App\NationalEvent\Command\GenerateTicketQRCodeCommand;
use App\Repository\NationalEvent\EventInscriptionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Endroid\QrCode\Builder\Builder;
use League\Flysystem\FilesystemOperator;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class GenerateTicketQRCodeCommandHandler
{
    public function __construct(
        private readonly EventInscriptionRepository $eventInscriptionRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly FilesystemOperator $nationalEventStorage,
    ) {
    }

    public function __invoke(GenerateTicketQRCodeCommand $command): void
    {
        /** @var EventInscription $eventInscription */
        if (!$eventInscription = $this->eventInscriptionRepository->findOneByUuid($command->getUuid()->toString())) {
            return;
        }

        // generate QR code
        $this->nationalEventStorage->write(
            $fileName = ($eventInscription->event->getSlug().'/'.$eventInscription->getUuid().'.png'),
            Builder::create()->data($eventInscription->getUuid()->toString())->build()->getString()
        );

        $eventInscription->ticketQRCodeFile = $fileName;

        $this->entityManager->flush();
    }
}
