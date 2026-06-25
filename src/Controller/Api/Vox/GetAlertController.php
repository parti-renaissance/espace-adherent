<?php

declare(strict_types=1);

namespace App\Controller\Api\Vox;

use App\Entity\Adherent;
use App\JeMengage\Alert\AlertProvider;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class GetAlertController extends AbstractController
{
    #[Route('/v3/alerts', name: 'api_vox_alert', methods: ['GET'])]
    #[Route('/alerts', name: 'api_alert_public', methods: ['GET'])]
    public function __invoke(AlertProvider $alertProvider, Security $security): Response
    {
        $user = $security->getUser();

        return $this->json($alertProvider->getAlerts($user instanceof Adherent ? $user : null));
    }
}
