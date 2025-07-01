<?php

namespace App\Normalizer;

use App\AdherentSpace\AdherentSpaceEnum;
use App\Entity\AdherentMessage\Filter\AudienceFilter;
use App\Geo\ManagedZoneProvider;
use App\Repository\CommitteeRepository;
use App\Scope\ScopeGeneratorResolver;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class AudienceFilterDenormalizer implements DenormalizerInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;

    public function __construct(
        private readonly ManagedZoneProvider $managedZoneProvider,
        private readonly Security $security,
        private readonly ScopeGeneratorResolver $scopeGeneratorResolver,
        private readonly CommitteeRepository $committeeRepository,
    ) {
    }

    public function denormalize($data, $type, $format = null, array $context = []): mixed
    {
        /** @var AudienceFilter $audienceFilter */
        $audienceFilter = $this->denormalizer->denormalize($data, $type, $format, $context + [__CLASS__ => true]);

        if ($scope = $this->scopeGeneratorResolver->generate()) {
            $audienceFilter->setZones($scope->getZones());
            if ($committeeUuids = $scope->getCommitteeUuids()) {
                $audienceFilter->setCommittee($this->committeeRepository->findOneByUuid(current($committeeUuids)));
            }

            if (!$audienceFilter->getScope()) {
                $audienceFilter->setScope($scope->getMainCode());
            }
        } else {
            if (($user = $this->security->getUser()) && $audienceFilter->getScope() && AdherentSpaceEnum::SCOPES[$audienceFilter->getScope()]) {
                $audienceFilter->setZones($this->managedZoneProvider->getManagedZones(
                    $user,
                    AdherentSpaceEnum::SCOPES[$audienceFilter->getScope()]
                ));
            }
        }

        return $audienceFilter;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            AudienceFilter::class => false,
        ];
    }

    public function supportsDenormalization($data, $type, $format = null, array $context = []): bool
    {
        return !isset($context[__CLASS__]) && AudienceFilter::class === $type;
    }
}
