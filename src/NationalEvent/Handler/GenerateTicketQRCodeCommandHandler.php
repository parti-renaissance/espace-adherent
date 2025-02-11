<?php

namespace App\NationalEvent\Handler;

use App\Entity\NationalEvent\EventInscription;
use App\NationalEvent\Command\GenerateTicketQRCodeCommand;
use App\QrCode\QrCodeResponseFactory;
use App\Repository\NationalEvent\EventInscriptionRepository;
use Doctrine\ORM\EntityManagerInterface;
use League\Flysystem\FilesystemOperator;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class GenerateTicketQRCodeCommandHandler
{
    public function __construct(
        private readonly EventInscriptionRepository $eventInscriptionRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly FilesystemOperator $nationalEventStorage,
        private readonly QrCodeResponseFactory $qrCodeResponseFactory,
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
            $this->qrCodeResponseFactory->getQrContent($eventInscription->getUuid()->toString())->getString()
        );

        $eventInscription->ticketQRCodeFile = $fileName;

        $this->entityManager->flush();
    }
}
