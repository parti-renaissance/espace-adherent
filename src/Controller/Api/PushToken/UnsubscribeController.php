<?php

declare(strict_types=1);

namespace App\Controller\Api\PushToken;

use App\Entity\Adherent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class UnsubscribeController extends AbstractController
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public function __invoke(#[CurrentUser] Adherent $adherent): Response
    {
        if ($currentSession = $adherent->currentAppSession) {
            $currentSession->unsubscribe();
            $this->entityManager->flush();
        }

        return $this->json([], Response::HTTP_OK);
    }
}
