<?php

namespace App\Controller\Api\Procuration;

use ApiPlatform\State\Pagination\PaginatorInterface;
use App\Entity\ProcurationV2\Request;
use App\Repository\Procuration\ProxyRepository;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

class GetMatchedProxiesController
{
    public function __invoke(SymfonyRequest $httpRequest, Request $request, ProxyRepository $repository): PaginatorInterface
    {
        return $repository->findAvailableProxies($request, max(1, $httpRequest->query->getInt('page', 1)));
    }
}
