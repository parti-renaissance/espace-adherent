<?php

namespace App\Normalizer\Pap;

use App\Entity\Pap\Campaign;
use App\Scope\ScopeGeneratorResolver;
use App\Scope\ScopeVisibilityEnum;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class CampaignDenormalizer implements DenormalizerInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;

    private const ALREADY_CALLED = 'PAP_CAMPAIGN_DENORMALIZER_ALREADY_CALLED';

    private ScopeGeneratorResolver $scopeGeneratorResolver;

    public function __construct(ScopeGeneratorResolver $scopeGeneratorResolver)
    {
        $this->scopeGeneratorResolver = $scopeGeneratorResolver;
    }

    public function denormalize($data, $class, $format = null, array $context = [])
    {
        $context[self::ALREADY_CALLED] = true;

        /** @var Campaign $data */
        $data = $this->denormalizer->denormalize($data, $class, $format, $context);
        if ('_api_/v3/pap_campaigns_post' === ($context['operation_name'] ?? null)
            && ($scope = $this->scopeGeneratorResolver->generate())) {
            if ($scope->isNational()) {
                $data->setVisibility(ScopeVisibilityEnum::NATIONAL);
            } else {
                $data->setVisibility(ScopeVisibilityEnum::LOCAL);
                $data->setZones($scope->getZones());
            }
        }

        return $data;
    }

    public function supportsDenormalization($data, $type, $format = null, array $context = [])
    {
        return
            empty($context[self::ALREADY_CALLED])
            && Campaign::class === $type;
    }
}
