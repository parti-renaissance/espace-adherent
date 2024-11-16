<?php

namespace App\Controller\Api;

use App\AdherentCharter\AdherentCharterFactory;
use App\AdherentCharter\AdherentCharterTypeEnum;
use App\CmsBlock\CmsBlockManager;
use App\Entity\Adherent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

class CharterController extends AbstractController
{
    #[IsGranted('CAN_ACCEPT_CHARTER', subject: 'type')]
    #[Route(path: '/v3/profile/charter/{type}', name: 'app_api_get_charter', methods: ['GET'])]
    public function retrieveCharter(
        string $type,
        TranslatorInterface $translator,
        CmsBlockManager $cmsBlockManager,
    ): Response {
        if (!AdherentCharterTypeEnum::isValid($type)) {
            return $this->json(
                ['message' => 'Le type de charte n\'est pas reconnu'],
                Response::HTTP_BAD_REQUEST
            );
        }

        /* @var Adherent $adherent */
        $adherent = $this->getUser();

        $fileUrl = $translator->trans($translationKey = \sprintf('%s.popup.file_url', $type));

        if ($translationKey === $fileUrl) {
            if ($adherent->getCharters()->hasCharterAcceptedForType($type)) {
                return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
            }

            return $this->json(['content' => $cmsBlockManager->getContent(\sprintf('chart-%s', $type))]);
        }

        return $this->json(['pdf' => $fileUrl]);
    }

    #[IsGranted('CAN_ACCEPT_CHARTER', subject: 'type')]
    #[Route(path: '/v3/profile/charter/{type}/accept', name: 'app_api_accept_charter', methods: ['PUT'])]
    public function acceptChart(string $type, EntityManagerInterface $entityManager): JsonResponse
    {
        if (!AdherentCharterTypeEnum::isValid($type)) {
            return $this->json(
                ['message' => 'Le type de charte n\'est pas reconnu'],
                Response::HTTP_BAD_REQUEST
            );
        }

        /* @var Adherent $adherent */
        $adherent = $this->getUser();
        if (!$adherent->getCharters()->hasCharterAcceptedForType($type)) {
            $adherent->addCharter(AdherentCharterFactory::create($type));
            $entityManager->flush();
        }

        return $this->json('OK');
    }
}
