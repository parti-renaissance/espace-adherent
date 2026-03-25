<?php

declare(strict_types=1);

namespace App\Normalizer;

use App\Entity\AdherentMessage\AdherentMessageFilter;
use App\Repository\CommitteeRepository;
use App\Scope\ScopeGeneratorResolver;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class AdherentMessageFilterDenormalizer implements DenormalizerInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;

    public const CONTEXT_FEATURE = 'filter_feature';

    public function __construct(
        private readonly ScopeGeneratorResolver $scopeGeneratorResolver,
        private readonly CommitteeRepository $committeeRepository,
    ) {
    }

    public function denormalize($data, $type, $format = null, array $context = []): mixed
    {
        /** @var AdherentMessageFilter $audienceFilter */
        $audienceFilter = $this->denormalizer->denormalize($data, $type, $format, $context + [__CLASS__ => true]);

        if ($scope = $this->scopeGeneratorResolver->generate()) {
            $scopeCode = $scope->getMainCode();

            if (!$scope->isNational() && $audienceFilter->getZones()->isEmpty() && $scope->getZones()) {
                $audienceFilter->setZones($scope->getZones());
            }

            if (!$audienceFilter->getCommittee() && ($committeeUuids = $scope->getCommitteeUuids()) && ($firstUuid = current($committeeUuids))) {
                $audienceFilter->setCommittee($this->committeeRepository->findOneByUuid($firstUuid));
            }

            if (!$audienceFilter->getScope() && $scopeCode) {
                $audienceFilter->setScope($scopeCode);
            }
        }

        return $audienceFilter;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            AdherentMessageFilter::class => false,
        ];
    }

    public function supportsDenormalization($data, $type, $format = null, array $context = []): bool
    {
        return !isset($context[__CLASS__]) && AdherentMessageFilter::class === $type;
    }
}
