<?php

namespace AppBundle\Referent;

use AppBundle\Entity\CitizenProject;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ManagedCitizenProjectsExporter
{
    private $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * @param CitizenProject[] $managedCitizenProjects
     */
    public function exportAsJson(array $managedCitizenProjects): string
    {
        $data = [];

        foreach ($managedCitizenProjects as $citizenProject) {
            $data[] = [
                'id' => $citizenProject->getId(),
                'name' => [
                    'label' => $citizenProject->getName(),
                    'url' => $this->urlGenerator->generate('app_citizen_project_show', [
                        'slug' => $citizenProject->getSlug(),
                    ]),
                ],
                'postalCode' => $citizenProject->getPostalCode(),
                'members' => $citizenProject->getMembersCount(),
            ];
        }

        return \GuzzleHttp\json_encode($data);
    }
}
