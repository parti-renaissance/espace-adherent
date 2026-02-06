<?php

declare(strict_types=1);

namespace App\Controller\Procuration;

use App\Entity\Procuration\Proxy as ProcurationProxy;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/mandataire/{uuid}/merci', name: 'app_procuration_proxy_thanks', methods: ['GET'])]
class ProxyThanksController extends AbstractController
{
    public function __invoke(ProcurationProxy $procurationProxy): Response
    {
        return $this->render('procuration/proxy_thanks.html.twig', [
            'procuration_proxy' => $procurationProxy,
        ]);
    }
}
