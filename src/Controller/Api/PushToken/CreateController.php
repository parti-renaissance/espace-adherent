<?php

declare(strict_types=1);

namespace App\Controller\Api\PushToken;

use App\Entity\Adherent;
use App\Entity\PushToken;
use App\Repository\PushTokenRepository;
use Doctrine\DBAL\Exception\DeadlockException;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
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
        if (!$token = $this->pushTokenRepository->findByIdentifier($data->identifier)) {
            $this->entityManager->persist($token = $data);
        }

        $user->currentAppSession?->addPushToken($token);

        try {
            $this->entityManager->flush();
        } catch (UniqueConstraintViolationException|DeadlockException) {
            return $this->json('', Response::HTTP_NO_CONTENT);
        }

        return $this->json('', Response::HTTP_CREATED);
    }
}
