<?php

namespace App\Controller\Api\PushToken;

use App\Entity\Adherent;
use App\Entity\PushToken;
use App\OAuth\Model\DeviceApiUser;
use App\Repository\PushTokenRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CreateController extends AbstractController
{
    public function __construct(
        private readonly ValidatorInterface $validator,
        private readonly EntityManagerInterface $entityManager,
        private readonly PushTokenRepository $pushTokenRepository,
    ) {
    }

    public function __invoke(PushToken $data, UserInterface $user): Response
    {
        if ($user instanceof Adherent) {
            $data->setAdherent($user);
        } elseif ($user instanceof DeviceApiUser) {
            $data->setDevice($user->getDevice());
        }

        $errors = $this->validator->validate($data);

        if ($errors->count() > 0) {
            return $this->json($errors, Response::HTTP_BAD_REQUEST);
        }

        if (!$token = $this->pushTokenRepository->findByIdentifier($data->getIdentifier())) {
            $this->entityManager->persist($token = $data);
        }

        if ($user instanceof Adherent && $user->currentAppSession && (!$token->appSession || $token->appSession !== $user->currentAppSession)) {
            $token->appSession = $user->currentAppSession;
        }

        $token->lastActiveDate = new \DateTime();
        $this->entityManager->flush();

        return $this->json([], Response::HTTP_CREATED);
    }
}
