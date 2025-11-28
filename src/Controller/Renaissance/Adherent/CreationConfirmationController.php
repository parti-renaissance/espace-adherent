<?php

declare(strict_types=1);

namespace App\Controller\Renaissance\Adherent;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/bienvenue', name: 'app_renaissance_adherent_creation_confirmation', methods: ['GET'])]
class CreationConfirmationController extends AbstractController
{
    public function __invoke(): Response
    {
        return $this->render('renaissance/adherent/creation_confirmation.html.twig');
    }
}
