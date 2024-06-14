<?php

namespace App\Controller\Api\Procuration;

use App\Entity\ProcurationV2\Request;
use App\Procuration\V2\ProcurationHandler;
use App\Repository\Procuration\RoundRepository;
use Ramsey\Uuid\Uuid;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Symfony\Component\HttpFoundation\Response;

class UnmatchRequestAndProxyController extends AbstractController
{
    public function __invoke(SymfonyRequest $httpRequest, Request $request, RoundRepository $roundRepository, ProcurationHandler $procurationHandler): Response
    {
        $data = json_decode($httpRequest->getContent(), true);

        if (empty($data['round']) || !Uuid::isValid($roundUuid = $data['round'])) {
            return $this->json([
                'status' => 'error',
                'message' => 'Identifiant de tour manquant ou invalide',
            ], Response::HTTP_BAD_REQUEST);
        }

        if (!$round = $roundRepository->findOneByUuid($roundUuid)) {
            return $this->json([
                'status' => 'error',
                'message' => 'Le tour n\'existe pas',
            ], Response::HTTP_BAD_REQUEST);
        }

        $procurationHandler->unmatch($request, $round, $data['email_copy'] ?? true);

        return $this->json('OK');
    }
}
