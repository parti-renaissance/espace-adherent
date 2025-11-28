<?php

declare(strict_types=1);

namespace App\Controller\Api\Procuration;

use App\Entity\ProcurationV2\Request;
use App\Procuration\V2\ProcurationHandler;
use App\Repository\Procuration\ProxyRepository;
use App\Repository\Procuration\RequestSlotRepository;
use App\Repository\Procuration\RoundRepository;
use Ramsey\Uuid\Uuid;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Symfony\Component\HttpFoundation\Response;

class MatchRequestWithProxyController extends AbstractController
{
    public function __invoke(
        SymfonyRequest $httpRequest,
        Request $request,
        ProxyRepository $proxyRepository,
        RoundRepository $roundRepository,
        RequestSlotRepository $requestSlotRepository,
        ProcurationHandler $procurationHandler,
    ): Response {
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

        if (empty($data['proxy']) || !Uuid::isValid($proxyUuid = $data['proxy'])) {
            return $this->json([
                'status' => 'error',
                'message' => 'Identifiant de mandataire manquant ou invalide',
            ], Response::HTTP_BAD_REQUEST);
        }

        if (!$proxy = $proxyRepository->findOneByUuid($proxyUuid)) {
            return $this->json([
                'status' => 'error',
                'message' => 'Le mandataire n\'existe pas',
            ], Response::HTTP_BAD_REQUEST);
        }

        if (!$proxy->isPending()) {
            return $this->json([
                'status' => 'error',
                'message' => 'Le mandataire n\'est pas disponible',
            ], Response::HTTP_BAD_REQUEST);
        }

        if ($requestSlotRepository->matchingAlreadyExists($request, $proxy, $round)) {
            return $this->json([
                'status' => 'error',
                'message' => 'Le mandataire est déjà relié à ce mandant pour ce tour',
            ], Response::HTTP_BAD_REQUEST);
        }

        $procurationHandler->match($request, $proxy, $round, $data['email_copy'] ?? true);

        return $this->json('OK');
    }
}
