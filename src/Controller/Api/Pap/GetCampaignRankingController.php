<?php

namespace App\Controller\Api\Pap;

use App\Entity\Adherent;
use App\Entity\Geo\Zone;
use App\Entity\Pap\Campaign;
use App\Repository\Pap\CampaignHistoryRepository;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/v3/pap_campaigns/{uuid}/ranking", requirements={"uuid": "%pattern_uuid%"}, name="api_get_pap_campaign_ranking", methods={"GET"})
 *
 * @Security("is_granted('ROLE_OAUTH_SCOPE_JEMARCHE_APP') and is_granted('ROLE_PAP_USER')")
 */
class GetCampaignRankingController extends AbstractController
{
    private CampaignHistoryRepository $campaignHistoryRepository;
    private LoggerInterface $logger;

    public function __construct(CampaignHistoryRepository $campaignHistoryRepository, LoggerInterface $logger)
    {
        $this->campaignHistoryRepository = $campaignHistoryRepository;
        $this->logger = $logger;
    }

    public function __invoke(Campaign $campaign): JsonResponse
    {
        /** @var Adherent $adherent */
        $adherent = $this->getUser();

        return $this->json([
            [
                'label' => 'Individuel',
                'fields' => [
                    'rank' => 'Rang',
                    'questioner' => 'Militant',
                    'nb_visited_doors' => 'Portes frappées',
                    'nb_surveys' => 'Questionnaires remplis',
                ],
                'items' => $this->createIndividualRanking($campaign, $adherent),
            ],
            [
                'label' => 'Département',
                'fields' => [
                    'rank' => 'Rang',
                    'department' => 'Département',
                    'nb_visited_doors' => 'Portes frappées',
                    'nb_surveys' => 'Questionnaires remplis',
                ],
                'items' => $this->createDepartmentalRanking($campaign, $adherent),
            ],
        ]);
    }

    private function createIndividualRanking(Campaign $campaign, Adherent $adherent): array
    {
        $items = $this->campaignHistoryRepository->findAdherentRanking($campaign);
        $adherentAdded = false;
        $individualItems = [];
        foreach ($items as $key => $data) {
            $item = [];
            $item['rank'] = ++$key;
            $item['questioner'] = $data['firstName'].(isset($data['lastName']) ? ' '.strtoupper(($data['lastName'][0])).'.' : '');
            $item['nb_visited_doors'] = (int) $data['nb_visited_doors'];
            $item['nb_surveys'] = (int) $data['nb_surveys'];
            $item['current'] = $isAdherent = $adherent->getId() === $data['id'];

            $adherentAdded = $adherentAdded ?: $isAdherent;
            $individualItems[] = $item;
        }

        if (!$adherentAdded) {
            $item = [];
            $adherentRank = $this->campaignHistoryRepository->findRankingForAdherent($campaign, $adherent);

            if (\count($adherentRank) > 0) {
                $item['nb_visited_doors'] = (int) $adherentRank[0]['nb_visited_doors'];
                $item['nb_surveys'] = (int) $adherentRank[0]['nb_surveys'];
            } else {
                $item['nb_visited_doors'] = 0;
                $item['nb_surveys'] = 0;
            }
            $item['rank'] = '...';
            $item['questioner'] = $adherent->getPartialName();
            $item['current'] = true;
            $individualItems[] = $item;
        }

        return $individualItems;
    }

    private function createDepartmentalRanking(Campaign $campaign, Adherent $adherent): array
    {
        $departments = $adherent->getParentZonesOfType(Zone::DEPARTMENT);
        $department = 1 === \count($departments) ? $departments[0] : null;
        if (!$department) {
            $this->logger->error(
                sprintf(
                    'Adherent with ID "%d" has no zone of type "department"',
                    $adherent->getId()
                )
            );

            return [];
        } elseif ('75' === $department->getCode()) {
            $boroughs = $adherent->getZonesOfType(Zone::BOROUGH);
            $borough = 1 === \count($boroughs) ? $boroughs[0] : null;
            if (!$borough) {
                $this->logger->error(
                    sprintf(
                        'Adherent with ID "%d" has no zone of type "borough"',
                        $adherent->getId()
                    )
                );

                return [];
            }

            $zone = $borough;
        } else {
            $zone = $department;
        }

        $items = $this->campaignHistoryRepository->findDepartmentRanking($campaign);
        $departmentAdded = false;
        $departmentalItems = [];
        foreach ($items as $key => $data) {
            $item = [];
            $item['rank'] = ++$key;
            $item['department'] = $data['name'];
            $item['nb_visited_doors'] = (int) $data['nb_visited_doors'];
            $item['nb_surveys'] = (int) $data['nb_surveys'];
            $item['current'] = $isAdherent = $adherent->getId() === $data['id'];

            $departmentAdded = $departmentAdded ?: $isAdherent;
            $departmentalItems[] = $item;
        }

        if (!$departmentAdded) {
            $item = [];
            $departmentRank = $this->campaignHistoryRepository->findRankingForDepartment($campaign, $zone);
            if (\count($departmentRank) > 0) {
                $item['nb_visited_doors'] = (int) $departmentRank[0]['nb_visited_doors'];
                $item['nb_surveys'] = (int) $departmentRank[0]['nb_surveys'];
            } else {
                $item['nb_visited_doors'] = 0;
                $item['nb_surveys'] = 0;
            }

            $item['rank'] = '...';
            $item['department'] = $department->getName();
            $item['current'] = true;
            $departmentalItems[] = $item;
        }

        return $departmentalItems;
    }
}
