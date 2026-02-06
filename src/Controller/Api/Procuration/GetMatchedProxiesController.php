<?php

declare(strict_types=1);

namespace App\Controller\Api\Procuration;

use ApiPlatform\State\Pagination\PaginatorInterface;
use App\Entity\Procuration\Request;
use App\Repository\Geo\ZoneRepository;
use App\Repository\Procuration\ProxyRepository;
use App\Repository\Procuration\RoundRepository;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class GetMatchedProxiesController
{
    public function __construct(
        private readonly RoundRepository $roundRepository,
        private readonly ProxyRepository $repository,
        private readonly ZoneRepository $zoneRepository,
    ) {
    }

    public function __invoke(SymfonyRequest $httpRequest, Request $request): PaginatorInterface
    {
        $roundUuid = $httpRequest->query->get('round');
        $search = trim($httpRequest->query->get('search', ''));
        $zone = trim($httpRequest->query->get('zone', ''));

        if (empty($roundUuid) || !Uuid::isValid($roundUuid)) {
            throw new BadRequestHttpException('Identifiant de tour manquant ou invalide');
        }

        if (!$round = $this->roundRepository->findOneByUuid($roundUuid)) {
            throw new BadRequestHttpException('Le tour n\'existe pas');
        }

        return $this->repository->findAvailableProxies(
            $request,
            $round,
            $search,
            Uuid::isValid($zone) ? $this->zoneRepository->findOneByUuid($zone) : null,
            max(1, $httpRequest->query->getInt('page', 1))
        );
    }
}
