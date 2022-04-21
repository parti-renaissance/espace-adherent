<?php

namespace App\Normalizer\Pap;

use App\Entity\Pap\Campaign;
use App\Pap\CampaignHistoryStatusEnum;
use App\Repository\Pap\BuildingStatisticsRepository;
use App\Repository\Pap\CampaignHistoryRepository;
use App\Scope\FeatureEnum;
use App\Scope\ScopeGeneratorResolver;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class AppendCampaignStatsNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    private const CAMPAIGN_ALREADY_CALLED = 'PAP_CAMPAIGN_NORMALIZER_ALREADY_CALLED';

    private CampaignHistoryRepository $campaignHistoryRepository;
    private BuildingStatisticsRepository $buildingStatisticsRepository;
    private ScopeGeneratorResolver $scopeGeneratorResolver;

    public function __construct(
        CampaignHistoryRepository $campaignHistoryRepository,
        BuildingStatisticsRepository $buildingStatisticsRepository,
        ScopeGeneratorResolver $scopeGeneratorResolver
    ) {
        $this->campaignHistoryRepository = $campaignHistoryRepository;
        $this->buildingStatisticsRepository = $buildingStatisticsRepository;
        $this->scopeGeneratorResolver = $scopeGeneratorResolver;
    }

    /**
     * @param Campaign $object
     */
    public function normalize($object, $format = null, array $context = [])
    {
        $context[self::CAMPAIGN_ALREADY_CALLED] = true;

        $campaign = $this->normalizer->normalize($object, $format, $context);

        $scope = $this->scopeGeneratorResolver->generate();

        if (!$scope || !$scope->containsFeatures([FeatureEnum::PAP, FeatureEnum::PAP_V2])) {
            return $campaign;
        }

        $zones = [];
        if (!$scope->isNational() && $object->isNationalVisibility()) {
            $zones = $scope->getZones();
        }

        $campaign['creator'] = $object->getCreator();
        $campaign['nb_surveys'] = $zones ? $this->campaignHistoryRepository->countCampaignHistoriesWithDataSurvey($object, $zones) : $object->getCampaignHistoriesWithDataSurvey()->count();
        $campaign['nb_visited_doors'] = $this->campaignHistoryRepository->countVisitedDoors($object, $zones);
        $campaign['nb_addresses'] = $object->getNbAddresses();
        $campaign['nb_voters'] = $object->getNbVoters();
        $campaign['nb_collected_contacts'] = $this->campaignHistoryRepository->countCollectedContacts($object, $zones);
        $campaign['nb_vote_places'] = $object->getVotePlaces()->count();
        if (($context['item_operation_name'] ?? null) === 'get') {
            $campaign['average_visit_time'] = $this->campaignHistoryRepository->findCampaignAverageVisitTime($object, $zones);
            $campaign['nb_open_doors'] = $this->campaignHistoryRepository->countOpenDoors($object, $zones);
            $campaign['nb_to_join'] = $zones ? $this->campaignHistoryRepository->countToJoinByCampaign($object, $zones) : $object->getCampaignHistoriesToJoin()->count();
            $campaign['nb_door_open'] = $zones ? $this->campaignHistoryRepository->countByCampaignAndStatus($object, CampaignHistoryStatusEnum::DOOR_OPEN, $zones) : $object->getCampaignHistoriesDoorOpen()->count();
            $campaign['nb_contact_later'] = $zones ? $this->campaignHistoryRepository->countByCampaignAndStatus($object, CampaignHistoryStatusEnum::CONTACT_LATER, $zones) : $object->getCampaignHistoriesContactLater()->count();

            $campaign = array_merge($campaign, $this->buildingStatisticsRepository->countByStatus($object));
        }

        return $campaign;
    }

    public function supportsNormalization($data, $format = null, array $context = [])
    {
        return
            empty($context[self::CAMPAIGN_ALREADY_CALLED])
            && $data instanceof Campaign
            && array_intersect(['pap_campaign_read', 'pap_campaign_read_list'], $context['groups'] ?? [])
        ;
    }
}
