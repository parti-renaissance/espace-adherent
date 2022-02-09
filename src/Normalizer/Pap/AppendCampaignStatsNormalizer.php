<?php

namespace App\Normalizer\Pap;

use App\Entity\Pap\Campaign;
use App\Pap\CampaignHistoryStatusEnum;
use App\Repository\Pap\CampaignHistoryRepository;
use App\Scope\FeatureEnum;
use App\Scope\ScopeGeneratorResolver;
use App\Security\Voter\FeatureVoter;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class AppendCampaignStatsNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    private const CAMPAIGN_ALREADY_CALLED = 'PAP_CAMPAIGN_NORMALIZER_ALREADY_CALLED';

    private AuthorizationCheckerInterface $authorizationChecker;
    private CampaignHistoryRepository $campaignHistoryRepository;
    private ScopeGeneratorResolver $scopeGeneratorResolver;

    public function __construct(
        CampaignHistoryRepository $campaignHistoryRepository,
        AuthorizationCheckerInterface $authorizationChecker,
        ScopeGeneratorResolver $scopeGeneratorResolver
    ) {
        $this->campaignHistoryRepository = $campaignHistoryRepository;
        $this->authorizationChecker = $authorizationChecker;
        $this->scopeGeneratorResolver = $scopeGeneratorResolver;
    }

    /**
     * @param Campaign $object
     */
    public function normalize($object, $format = null, array $context = [])
    {
        $context[self::CAMPAIGN_ALREADY_CALLED] = true;

        $campaign = $this->normalizer->normalize($object, $format, $context);

        if (!$this->authorizationChecker->isGranted(FeatureVoter::PERMISSION, FeatureEnum::PAP)) {
            return $campaign;
        }

        $scope = $this->scopeGeneratorResolver->generate();
        $zones = [];
        if (!$scope->isNational() && $object->isNationalVisibility()) {
            $zones = $scope->getZones();
        }

        $campaign['nb_surveys'] = $zones ? $this->campaignHistoryRepository->countCampaignHistoriesWithDataSurvey($object, $zones) : $object->getCampaignHistoriesWithDataSurvey()->count();
        $campaign['nb_visited_doors'] = $this->campaignHistoryRepository->countVisitedDoors($object, $zones);
        $campaign['nb_addresses'] = $object->getNbAddresses();
        $campaign['nb_voters'] = $object->getNbVoters();
        if (($context['item_operation_name'] ?? null) === 'get') {
            $campaign['nb_collected_contacts'] = $this->campaignHistoryRepository->countCollectedContacts($object, $zones);
            $campaign['average_visit_time'] = $this->campaignHistoryRepository->findCampaignAverageVisitTime($object, $zones);
            $campaign['nb_open_doors'] = $this->campaignHistoryRepository->countOpenDoors($object, $zones);
            $campaign['nb_to_join'] = $zones ? $this->campaignHistoryRepository->countToJoinByCampaign($object, $zones) : $object->getCampaignHistoriesToJoin()->count();
            $campaign['nb_door_open'] = $zones ? $this->campaignHistoryRepository->countByCampaignAndStatus($object, CampaignHistoryStatusEnum::DOOR_OPEN, $zones) : $object->getCampaignHistoriesDoorOpen()->count();
            $campaign['nb_contact_later'] = $zones ? $this->campaignHistoryRepository->countByCampaignAndStatus($object, CampaignHistoryStatusEnum::CONTACT_LATER, $zones) : $object->getCampaignHistoriesContactLater()->count();
        }

        return $campaign;
    }

    public function supportsNormalization($data, $format = null, array $context = [])
    {
        return
            empty($context[self::CAMPAIGN_ALREADY_CALLED])
            && $data instanceof Campaign
            && \in_array('pap_campaign_read', $context['groups'] ?? [])
        ;
    }
}
