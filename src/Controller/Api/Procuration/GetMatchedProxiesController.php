<?php

namespace App\Controller\Api\Procuration;

use ApiPlatform\State\Pagination\PaginatorInterface;
use App\Entity\ProcurationV2\Request;
use App\Repository\Procuration\ProxyRepository;
use App\Repository\Procuration\RoundRepository;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class GetMatchedProxiesController
{
    public function __invoke(SymfonyRequest $httpRequest, Request $request, RoundRepository $roundRepository, ProxyRepository $repository): PaginatorInterface
    {
        $roundUuid = $httpRequest->query->get('round');
        if (empty($roundUuid) || !Uuid::isValid($roundUuid)) {
            throw new BadRequestHttpException('Identifiant de tour manquant ou invalide');
        }

        if (!$round = $roundRepository->findOneByUuid($roundUuid)) {
            throw new BadRequestHttpException('Le tour n\'existe pas');
        }

        return $repository->findAvailableProxies($request, $round, max(1, $httpRequest->query->getInt('page', 1)));
    }
}
