<?php

namespace App\Controller\Api\Procuration;

use App\Entity\ProcurationV2\Request;
use App\Procuration\V2\ProcurationHandler;
use App\Repository\Procuration\ProxyRepository;
use Doctrine\ORM\EntityManagerInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Symfony\Component\HttpFoundation\Response;

class MatchRequestWithProxyController extends AbstractController
{
    public function __invoke(SymfonyRequest $httpRequest, Request $request, ProxyRepository $repository, EntityManagerInterface $entityManager, ProcurationHandler $procurationHandler): Response
    {
        $data = json_decode($httpRequest->getContent(), true);

        if (empty($data['proxy']) || !Uuid::isValid($uuid = $data['proxy'])) {
            return $this->json([
                'status' => 'error',
                'message' => 'Identifiant manquant ou invalide',
            ], Response::HTTP_BAD_REQUEST);
        }

        if (!$proxy = $repository->findOneByUuid($uuid)) {
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

        if ($proxy->hasRequest($request)) {
            return $this->json([
                'status' => 'error',
                'message' => 'Le mandataire est déjà relié à ce mandant',
            ], Response::HTTP_BAD_REQUEST);
        }

        $procurationHandler->match($request, $proxy);

        return $this->json('OK');
    }
}
