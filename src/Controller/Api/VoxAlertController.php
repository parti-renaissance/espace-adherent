<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\JeMengage\Alert\AlertProvider;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

#[Route('/v3/alerts', name: 'api_vox_alert', methods: ['GET'])]
class VoxAlertController extends AbstractController
{
    public function __invoke(UserInterface $user, AlertProvider $alertProvider): Response
    {
        return $this->json($alertProvider->getAlerts($user));
    }
}
