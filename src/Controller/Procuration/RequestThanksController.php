<?php

declare(strict_types=1);

namespace App\Controller\Procuration;

use App\Entity\Procuration\Request as ProcurationRequest;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/mandant/{uuid}/merci', name: 'app_procuration_request_thanks', methods: ['GET'])]
class RequestThanksController extends AbstractController
{
    public function __invoke(ProcurationRequest $procurationRequest): Response
    {
        return $this->render('procuration/request_thanks.html.twig', [
            'procuration_request' => $procurationRequest,
        ]);
    }
}
