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

    protected const ALREADY_CALLED = 'BUILDING_BLOCK_NORMALIZER_ALREADY_CALLED';

    private RequestStack $requestStack;
    private CampaignRepository $campaignRepository;

    public function __construct(RequestStack $requestStack, CampaignRepository $campaignRepository)
    {
        $this->requestStack = $requestStack;
        $this->campaignRepository = $campaignRepository;
    }

    /**
     * @param BuildingBlock $object
     */
    public function normalize($object, $format = null, array $context = [])
    {
        $context[static::ALREADY_CALLED] = true;
        $campaign = null;

        if ($campaignUuid = $this->requestStack->getMainRequest()->query->get('campaign_uuid')) {
            if ($campaign = $this->campaignRepository->findOneByUuid($campaignUuid)) {
                $context['campaign'] = $campaign;
            }
        }

        $data = $this->normalizer->normalize($object, $format, $context);

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
            '*' => null,
            BuildingBlock::class => false,
        ];
    }

    public function supportsNormalization($data, $format = null, array $context = []): bool
    {
        return $data instanceof BuildingBlock && !isset($context[static::ALREADY_CALLED]);
    }
}
