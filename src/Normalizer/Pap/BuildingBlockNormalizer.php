<?php

namespace App\Normalizer\Pap;

use App\Entity\Pap\BuildingBlock;
use App\Repository\Pap\CampaignRepository;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class BuildingBlockNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly CampaignRepository $campaignRepository,
    ) {
    }

    /**
     * @param BuildingBlock $object
     */
    public function normalize($object, $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        $campaign = null;

        if ($campaignUuid = $this->requestStack->getMainRequest()->query->get('campaign_uuid')) {
            if ($campaign = $this->campaignRepository->findOneByUuid($campaignUuid)) {
                $context['campaign'] = $campaign;
            }
        }

        $data = $this->normalizer->normalize($object, $format, $context + [__CLASS__ => true]);

        if ($campaign && $campaignUuid && $stats = $object->findStatisticsForCampaign($campaign)) {
            $data['campaign_statistics'] = [
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
            BuildingBlock::class => false,
        ];
    }

    public function supportsNormalization($data, $format = null, array $context = []): bool
    {
        return !isset($context[__CLASS__]) && $data instanceof BuildingBlock;
    }
}
