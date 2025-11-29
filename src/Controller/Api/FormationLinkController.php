<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Entity\AdherentFormation\Formation;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;

class FormationLinkController extends AbstractFormationContentController
{
    public function __invoke(
        SerializerInterface $serializer,
        Formation $formation,
    ): Response {
        if (!$formation->isLinkContent()) {
            throw $this->createNotFoundException('Formation has no link');
        }

        $this->printFormation($formation);

        return $this->json(
            $formation,
            Response::HTTP_OK,
            [],
            ['groups' => ['formation_read_link']]
        );
    }
}
