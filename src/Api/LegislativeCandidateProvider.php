<?php

namespace AppBundle\Api;

use AppBundle\Entity\LegislativeCandidate;
use AppBundle\Repository\LegislativeCandidateRepository;
use AppBundle\Twig\AssetRuntime;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class LegislativeCandidateProvider
{
    private $repository;
    private $asset;
    private $urlGenerator;

    public function __construct(
        LegislativeCandidateRepository $repository,
        AssetRuntime $asset,
        UrlGeneratorInterface $urlGenerator
    ) {
        $this->repository = $repository;
        $this->asset = $asset;
        $this->urlGenerator = $urlGenerator;
    }

    public function getForApi(): array
    {
        foreach ($this->repository->findAllForDirectory(LegislativeCandidate::STATUS_WON) as $candidate) {
            if (!$candidate->getGeojson()) {
                continue;
            }

            $data[] = [
                'id' => $candidate->getId(),
                'name' => $candidate->getFullName(),
                'district' => $candidate->getDistrictName(),
                'picture' => $candidate->getMedia() ? $this->asset->transformedMediaAsset($candidate->getMedia(), ['w' => 200, 'h' => 140, 'q' => 70, 'fit' => 'crop']) : '',
                'url' => $this->urlGenerator->generate('legislatives_candidate', ['slug' => $candidate->getSlug()]),
                'geojson' => \GuzzleHttp\json_decode($candidate->getGeojson()),
            ];
        }

        return $data ?? [];
    }
}
