<?php

declare(strict_types=1);

namespace App\Controller\Api\Pap;

use App\Entity\Adherent;
use App\Entity\Geo\Zone;
use App\Entity\Geo\ZoneTagEnum;
use App\Entity\Pap\Campaign;
use App\Repository\Geo\ZoneRepository;
use App\Repository\Pap\CampaignHistoryRepository;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted(new Expression("is_granted('ROLE_OAUTH_SCOPE_JEMARCHE_APP') and is_granted('ROLE_PAP_USER')"))]
#[Route(path: '/v3/pap_campaigns/{uuid}/ranking', name: 'api_get_pap_campaign_ranking', requirements: ['uuid' => '%pattern_uuid%'], methods: ['GET'])]
class GetCampaignRankingController extends AbstractController
{
    private CampaignHistoryRepository $campaignHistoryRepository;
    private ZoneRepository $zoneRepository;
    private LoggerInterface $logger;

    public function __construct(
        CampaignHistoryRepository $campaignHistoryRepository,
        ZoneRepository $zoneRepository,
        LoggerInterface $logger,
    ) {
        $this->campaignHistoryRepository = $campaignHistoryRepository;
        $this->zoneRepository = $zoneRepository;
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
            $item['questioner'] = $data['firstName'].(isset($data['lastName']) ? ' '.strtoupper($data['lastName'][0]).'.' : '');
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
        $adherentZoneIds = [];
        $zone = $adherent->getParisBoroughOrDepartment();

        if ($zone) {
            $adherentZoneIds = [$zone->getId()];
        } else {
            $this->logger->error(\sprintf('Adherent with ID "%d" has neither zone of type "department" nor "borough"', $adherent->getId()));
        }

        $zonesList = array_column(array_map(function (Zone $zone) {
            return [
                'id' => $zone->getId(),
                'name' => $zone->getName(),
                'nb_visited_doors' => 0,
                'nb_surveys' => 0,
            ];
        }, $this->zoneRepository->findByTag(ZoneTagEnum::DEPARTMENT_BOROUGH_LIST)), null, 'id');

        foreach ($this->campaignHistoryRepository->findDepartmentRanking($campaign, $scoreBordSize = 10) as $item) {
            $zonesList[$item['id']]['nb_visited_doors'] = (int) $item['nb_visited_doors'];
            $zonesList[$item['id']]['nb_surveys'] = (int) $item['nb_surveys'];
        }

        usort($zonesList, function (array $a, array $b) {
            if ($a['nb_surveys'] === $b['nb_surveys']) {
                return strcasecmp($a['name'], $b['name']);
            }

            return $b['nb_surveys'] <=> $a['nb_surveys'];
        });

        $departmentAdded = false;
        $departmentalItems = [];

        foreach (\array_slice($zonesList, 0, $scoreBordSize) as $key => $data) {
            $item = [];
            $item['rank'] = ++$key;
            $item['department'] = $data['name'];
            $item['nb_visited_doors'] = $data['nb_visited_doors'];
            $item['nb_surveys'] = $data['nb_surveys'];
            $item['current'] = $isAdherent = \in_array($data['id'], $adherentZoneIds);

            $departmentAdded = $departmentAdded ?: $isAdherent;
            $departmentalItems[] = $item;
        }

        if (!$departmentAdded && $zone) {
            $item = [];
            $item['rank'] = '...';
            $item['department'] = $zone->getName();

            $departmentRank = $this->campaignHistoryRepository->findRankingForDepartment($campaign, $zone);
            if (\count($departmentRank) > 0) {
                $item['nb_visited_doors'] = (int) $departmentRank[0]['nb_visited_doors'];
                $item['nb_surveys'] = (int) $departmentRank[0]['nb_surveys'];
            } else {
                $item['nb_visited_doors'] = 0;
                $item['nb_surveys'] = 0;
            }
            $item['current'] = true;

            $departmentalItems[\count($departmentalItems) - 1] = $item;
        }

        return $departmentalItems;
    }
}
