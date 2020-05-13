<?php

namespace App\Api;

use App\Repository\CommitteeRepository;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class CommitteeProvider
{
    private $repository;
    private $urlGenerator;

    public function __construct(CommitteeRepository $repository, UrlGeneratorInterface $urlGenerator)
    {
        $this->repository = $repository;
        $this->urlGenerator = $urlGenerator;
    }

    public function getApprovedCommittees(): array
    {
        $data = [];

        foreach ($this->repository->findApprovedCommittees() as $committee) {
            if (!$committee->isGeocoded()) {
                continue;
            }

            $data[] = [
                'uuid' => $committee->getUuid()->toString(),
                'slug' => $committee->getSlug(),
                'name' => $committee->getName(),
                'url' => $this->urlGenerator->generate('app_committee_show', ['slug' => $committee->getSlug()]),
                'position' => [
                    'lat' => (float) $committee->getLatitude(),
                    'lng' => (float) $committee->getLongitude(),
                ],
            ];
        }

        return $data;
    }
}
