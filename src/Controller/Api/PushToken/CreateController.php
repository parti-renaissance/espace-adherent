<?php

namespace App\Controller\Api\PushToken;

use App\Entity\Adherent;
use App\Entity\PushToken;
use App\Repository\PushTokenRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class CreateController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly PushTokenRepository $pushTokenRepository,
    ) {
    }

    public function __invoke(PushToken $data, #[CurrentUser] Adherent $user): Response
    {
        $data->setAdherent($user);

        if (!$token = $this->pushTokenRepository->findByIdentifier($data->getIdentifier())) {
            $this->entityManager->persist($token = $data);
        }

        if ($user->currentAppSession && (!$token->appSession || $token->appSession !== $user->currentAppSession)) {
            $token->appSession = $user->currentAppSession;
        }

        $token->appSession?->subscribe();
        $token->lastActiveDate = new \DateTime();
        $this->entityManager->flush();

        return $this->json([], Response::HTTP_CREATED);
    }
}
