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

    public function __construct(private readonly ScopeGeneratorResolver $scopeGeneratorResolver)
    {
    }

    public function denormalize($data, $class, $format = null, array $context = []): mixed
    {
        /** @var Campaign $data */
        $data = $this->denormalizer->denormalize($data, $class, $format, $context + [__CLASS__ => true]);
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

    public function getSupportedTypes(?string $format): array
    {
        return [
            Campaign::class => false,
        ];
    }

    public function supportsDenormalization($data, $type, $format = null, array $context = []): bool
    {
        return !isset($context[__CLASS__]) && Campaign::class === $type;
    }
}
