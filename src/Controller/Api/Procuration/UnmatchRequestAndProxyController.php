<?php

namespace App\Controller\Api\Procuration;

use App\Entity\ProcurationV2\Request;
use App\Procuration\V2\ProcurationHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Symfony\Component\HttpFoundation\Response;

class UnmatchRequestAndProxyController extends AbstractController
{
    public function __invoke(SymfonyRequest $httpRequest, Request $request, ProcurationHandler $procurationHandler): Response
    {
        $data = json_decode($httpRequest->getContent(), true);

        $procurationHandler->unmatch($request, $data['email_copy'] ?? true);

        return $this->json('OK');
    }
}
