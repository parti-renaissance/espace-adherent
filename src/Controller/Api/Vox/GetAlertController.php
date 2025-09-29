<?php

declare(strict_types=1);

namespace App\Controller\Api\Vox;

use App\Entity\Adherent;
use App\JeMengage\Alert\AlertProvider;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route('/v3/alerts', name: 'api_vox_alert', methods: ['GET'])]
class GetAlertController extends AbstractController
{
    public function __invoke(#[CurrentUser] Adherent $user, AlertProvider $alertProvider): Response
    {
        return $this->json($alertProvider->getAlerts($user));
    }
}
