<?php

namespace App\Controller\Api;

use App\AdherentCharter\AdherentCharterFactory;
use App\AdherentCharter\AdherentCharterTypeEnum;
use App\Entity\Adherent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class CharterController extends AbstractController
{
    /**
     * @Route("/v3/profile/charter/{type}", name="app_api_get_charter", methods={"GET"})
     */
    public function retrieveCharter(string $type, TranslatorInterface $translator): Response
    {
        if (!AdherentCharterTypeEnum::isValid($type)) {
            return $this->json(
                ['message' => 'Le type de charte n\'est pas réconnu'],
                Response::HTTP_BAD_REQUEST
            );
        }

        /* @var Adherent $adherent */
        $adherent = $this->getUser();

        if (AdherentCharterTypeEnum::TYPE_PHONING_CAMPAIGN === $type) {
            if ($adherent->getCharters()->hasPhoningCampaignCharterAccepted()) {
                return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
            }

            return $this->json(
                ['content' => '**Texte de la charte** pour la *campagne* de phoning avec le Markdown'],
                JsonResponse::HTTP_OK
            );
        }

        return $this->redirect($translator->trans(sprintf('%s.popup.file_url', $type)));
    }

    /**
     * @Route("/v3/profile/charter/{type}/accept", name="app_api_accept_charter", methods={"PUT"})
     */
    public function acceptChart(string $type, EntityManagerInterface $entityManager): JsonResponse
    {
        if (!AdherentCharterTypeEnum::isValid($type)) {
            return $this->json(
                ['message' => 'Le type de charte n\'est pas réconnu'],
                Response::HTTP_BAD_REQUEST
            );
        }

        /* @var Adherent $adherent */
        $adherent = $this->getUser();
        if (!$adherent->getCharters()->hasCharterAcceptedForType($type)) {
            $adherent->addCharter(AdherentCharterFactory::create($type));
            $entityManager->flush();
        }

        return new JsonResponse('OK', Response::HTTP_OK);
    }
}
