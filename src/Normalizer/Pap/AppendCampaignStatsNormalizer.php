<?php

namespace App\Normalizer\Pap;

use App\Entity\Pap\Campaign;
use App\Repository\Pap\CampaignHistoryRepository;
use App\Scope\FeatureEnum;
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

    public function __construct(
        CampaignHistoryRepository $campaignHistoryRepository,
        AuthorizationCheckerInterface $authorizationChecker
    ) {
        $this->campaignHistoryRepository = $campaignHistoryRepository;
        $this->authorizationChecker = $authorizationChecker;
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

        $campaign['nb_surveys'] = $object->getCampaignHistoriesWithDataSurvey()->count();
        $campaign['nb_visited_doors'] = $this->campaignHistoryRepository->countVisitedDoors($object);
        $campaign['nb_collected_contacts'] = $this->campaignHistoryRepository->countCollectedContacts($object);
        $campaign['average_visit_time'] = $this->campaignHistoryRepository->findCampaignAverageVisitTime($object);

        return $campaign;
    }

    public function supportsNormalization($data, $format = null, array $context = [])
    {
        return
            empty($context[self::CAMPAIGN_ALREADY_CALLED])
            && $data instanceof Campaign
            && \in_array('pap_campaign_read', $context['groups'] ?? [])
            && ($context['item_operation_name'] ?? null) === 'get'
        ;
    }
}
