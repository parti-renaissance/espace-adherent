<?php

namespace App\Controller\Api\PushToken;

use App\Entity\PushToken;
use App\Repository\PushTokenRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CreateController extends AbstractController
{
    public function __construct(
        private readonly ValidatorInterface $validator,
        private readonly EntityManagerInterface $entityManager,
        private readonly PushTokenRepository $pushTokenRepository,
    ) {
    }

    public function __invoke(PushToken $data): Response
    {
        $data->setAdherent($this->getUser());

        $errors = $this->validator->validate($data);

        if ($errors->count() > 0) {
            return $this->json($errors, Response::HTTP_BAD_REQUEST);
        }

        if (!$token = $this->pushTokenRepository->findByIdentifier($data->getIdentifier())) {
            $this->entityManager->persist($token = $data);
        }

        $token->lastActiveDate = new \DateTime();
        $this->entityManager->flush();

        return $this->json([], Response::HTTP_CREATED);
    }
}
