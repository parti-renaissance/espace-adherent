<?php

namespace App\Normalizer\Pap;

use App\Entity\Pap\Floor;
use App\Entity\Pap\FloorStatistics;
use App\Repository\Pap\CampaignRepository;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class BuildingFloorNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly CampaignRepository $campaignRepository,
    ) {
    }

    /**
     * @param Floor $object
     */
    public function normalize($object, $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        $data = $this->normalizer->normalize($object, $format, $context + [__CLASS__ => true]);

        $campaign = null;
        $campaignUuid = $this->requestStack->getMainRequest()->query->get('campaign_uuid');
        if (!empty($context['campaign'])) {
            $campaign = $context['campaign'];
        } else {
            if ($campaignUuid) {
                $campaign = $this->campaignRepository->findOneByUuid($campaignUuid);
            }
        }

        /** @var FloorStatistics $stats */
        if ($campaign && $campaignUuid && $stats = $object->findStatisticsForCampaign($campaign)) {
            $data['campaign_statistics'] = [
                'visited_doors' => $stats->getVisitedDoors(),
                'nb_surveys' => $stats->getNbSurveys(),
                'status' => $stats->getStatus(),
                'closed_at' => $stats->getClosedAt() ? $stats->getClosedAt()->format(\DateTime::RFC3339) : null,
                'closed_by' => $stats->getClosedBy() ? $stats->getClosedBy()->getPartialName() : null,
            ];
        }

        return $data;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            Floor::class => false,
        ];
    }

    public function supportsNormalization($data, $format = null, array $context = []): bool
    {
        return !isset($context[__CLASS__]) && $data instanceof Floor;
    }
}
