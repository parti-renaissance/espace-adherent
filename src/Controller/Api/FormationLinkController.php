<?php

namespace App\Controller\Api;

use App\Entity\AdherentFormation\Formation;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

class FormationLinkController extends AbstractFormationContentController
{
    public function __invoke(
        Request $request,
        SerializerInterface $serializer,
        Formation $formation,
    ): Response {
        if (!$formation->isLinkContent()) {
            throw $this->createNotFoundException('Formation has no link');
        }

        $this->printFormation($formation);

        return JsonResponse::fromJsonString(
            $serializer->serialize($formation, 'json', [
                AbstractNormalizer::GROUPS => ['formation_read_link'],
            ])
        );
    }
}
