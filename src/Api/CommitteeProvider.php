<?php

namespace AppBundle\Api;

use AppBundle\Committee\CommitteeUrlGenerator;
use AppBundle\Repository\CommitteeRepository;

class CommitteeProvider
{
    private $repository;
    private $urlGenerator;

    public function __construct(CommitteeRepository $repository, CommitteeUrlGenerator $urlGenerator)
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
                'url' => $this->urlGenerator->getPath('app_committee_show', $committee),
                'position' => [
                    'lat' => (float) $committee->getLatitude(),
                    'lng' => (float) $committee->getLongitude(),
                ],
            ];
        }

        return $data;
    }
}
