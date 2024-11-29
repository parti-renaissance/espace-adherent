<?php

namespace App\Controller\Api;

use App\Adherent\Command\SendResubscribeEmailCommand;
use App\Entity\Adherent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;

class SendResubscribeEmailController extends AbstractController
{
    public function __invoke(MessageBusInterface $bus, EntityManagerInterface $entityManager, Adherent $adherent): Response
    {
        if ($adherent->isEmailSubscribed()) {
            return $this->json(['message' => 'Militant est déjà abonné aux emails'], Response::HTTP_BAD_REQUEST);
        }

        if ($adherent->resubscribeEmailSentAt && $adherent->resubscribeEmailSentAt->diff(new \DateTime())->y < 1) {
            return $this->json(['message' => 'Un autre email de réabonnement a déjà été envoyé'], Response::HTTP_BAD_REQUEST);
        }

        $bus->dispatch(new SendResubscribeEmailCommand($adherent));
        $adherent->resubscribeEmailSentAt = new \DateTime();

        $entityManager->flush();

        return $this->json('OK', Response::HTTP_OK);
    }
}
