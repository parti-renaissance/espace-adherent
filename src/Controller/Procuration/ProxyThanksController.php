<?php

namespace App\Controller\Procuration;

use App\Controller\CanaryControllerTrait;
use App\Entity\Procuration\Proxy as ProcurationProxy;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/mandataire/{uuid}/merci', name: 'app_procuration_v2_proxy_thanks', methods: ['GET'])]
class ProxyThanksController extends AbstractController
{
    use CanaryControllerTrait;

    public function __invoke(ProcurationProxy $procurationProxy): Response
    {
        $this->disableInProduction();

        return $this->render('procuration_v2/proxy_thanks.html.twig', [
            'procuration_proxy' => $procurationProxy,
        ]);
    }
}
