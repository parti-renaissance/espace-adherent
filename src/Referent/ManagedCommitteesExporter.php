<?php

namespace App\Referent;

use App\Entity\Committee;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ManagedCommitteesExporter
{
    private $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * @param Committee[] $managedCommittees
     */
    public function exportAsJson(array $managedCommittees): string
    {
        $data = [];

        foreach ($managedCommittees as $committee) {
            $data[] = [
                'id' => $committee->getId(),
                'name' => [
                    'label' => $committee->getName(),
                    'url' => $this->urlGenerator->generate('app_committee_show', [
                        'slug' => $committee->getSlug(),
                    ]),
                ],
                'postalCode' => $committee->getPostalCode(),
                'members' => $committee->getMembersCount(),
            ];
        }

        return \GuzzleHttp\json_encode($data);
    }
}
