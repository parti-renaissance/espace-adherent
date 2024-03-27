<?php

namespace App\Controller\Api\Procuration;

use App\Entity\ProcurationV2\Request;
use App\Procuration\V2\ProcurationHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class UnmatchRequestAndProxyController extends AbstractController
{
    public function __invoke(Request $request, ProcurationHandler $procurationHandler): Response
    {
        $procurationHandler->unmatch($request);

        return $this->json('OK');
    }
}
