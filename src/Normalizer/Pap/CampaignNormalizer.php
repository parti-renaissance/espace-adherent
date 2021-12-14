<?php

namespace App\Normalizer\Pap;

use App\Entity\Pap\Campaign;
use App\Repository\Pap\CampaignHistoryRepository;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class CampaignNormalizer implements NormalizerInterface, NormalizerAwareInterface
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

        if (!$this->authorizationChecker->isGranted('IS_FEATURE_GRANTED', 'pap')) {
            return $this->normalizer->normalize($object, $format, $context);
        }

        $campaign = $this->normalizer->normalize($object, $format, $context);

        if (isset($context['item_operation_name']) && \in_array($context['item_operation_name'], ['get', 'put'])) {
            $stats = [
                'nb_surveys' => $object->getCampaignHistoriesWithDataSurvey()->count(),
                'nb_visited_doors' => $this->campaignHistoryRepository->countVisitedDoors($object),
                'nb_collected_contacts' => $this->campaignHistoryRepository->countCollectedContacts($object),
                'average_visit_time' => $this->campaignHistoryRepository->findCampaignAverageVisitTime($object),
            ];

            $campaign = array_merge($campaign, $stats);
        }

        if (isset($context['collection_operation_name']) && 'post' === $context['collection_operation_name']) {
            $stats = [
                'nb_surveys' => $object->getCampaignHistoriesWithDataSurvey()->count(),
                'nb_visited_doors' => 0,
                'nb_collected_contacts' => 0,
                'average_visit_time' => 0,
            ];
            $campaign = array_merge($campaign, $stats);
        }

        return $campaign;
    }

    public function supportsNormalization($data, $format = null, array $context = [])
    {
        return
            empty($context[self::CAMPAIGN_ALREADY_CALLED])
            && $data instanceof Campaign
        ;
    }
}
