<?php

namespace App\Controller\Api\PushToken;

use App\Entity\PushToken;
use App\Repository\PushTokenRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CreateController extends AbstractController
{
    public function __construct(
        private readonly ValidatorInterface $validator,
        private readonly PushTokenRepository $pushTokenRepository,
    ) {
    }

    public function __invoke(PushToken $data): Response|PushToken
    {
        $errors = $this->validator->validate($data);

        if ($errors->count() > 0) {
            return $this->json($errors, Response::HTTP_BAD_REQUEST);
        }

        if (!$this->pushTokenRepository->findByIdentifier($data->getIdentifier())) {
            return $data;
        }

        return $this->json([], Response::HTTP_CREATED);
    }
}
