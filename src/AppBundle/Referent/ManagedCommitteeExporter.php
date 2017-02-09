<?php

namespace AppBundle\Referent;

use AppBundle\Entity\Committee;
use Symfony\Bundle\FrameworkBundle\Routing\Router;

class ManagedCommitteeExporter
{
    /**
     * @var Router
     */
    private $router;

    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    /**
     * @param Committee[] $managedCommittees
     *
     * @return string
     */
    public function exportAsJson(array $managedCommittees): string
    {
        $data = [];

        foreach ($managedCommittees as $committee) {
            $data[] = [
                'id' => $committee->getId(),
                'postalCode' => $committee->getPostalCode(),
                'name' => $committee->getName(),
            ];
        }

        return \GuzzleHttp\json_encode($data);
    }
}
