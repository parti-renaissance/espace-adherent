<?php

namespace App\Controller\Api\Phoning;

use App\Entity\Jecoute\CampaignHistory;
use App\Entity\Phoning\Campaign;
use App\Phoning\DataSurveyStatusEnum;
use App\Repository\AdherentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Zend\Diactoros\Response\JsonResponse;

/**
 * @Route("/v3/phoning_campaigns/{uuid}/start", name="app_start_phoning_campaign", requirements={"uuid": "%pattern_uuid%"}, methods={"POST"})
 * @Security("has_role('ROLE_PHONING')")
 */
class StartPhoningCampaignController extends AbstractController
{
    public function __invoke(
        Campaign $campaign,
        UserInterface $adherent,
        AdherentRepository $adherentRepository,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        $adherent = $adherentRepository->findOneToCall($campaign);

        $phoningDataSurvey = new CampaignHistory();
        $phoningDataSurvey->setAdherent($adherent);
        $phoningDataSurvey->setStatus(DataSurveyStatusEnum::SEND);
        $phoningDataSurvey->setBeginAt(new \DateTime());

        $entityManager->persist($phoningDataSurvey);
        $entityManager->flush();

        return $this->json(
             $phoningDataSurvey,
            Response::HTTP_OK,
            [],
            ['groups' => ['start_phoning_campaign']]
        );
    }
}
