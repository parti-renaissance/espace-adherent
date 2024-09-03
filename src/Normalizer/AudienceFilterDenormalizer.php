<?php

namespace App\Normalizer;

use App\AdherentSpace\AdherentSpaceEnum;
use App\Entity\AdherentMessage\Filter\AudienceFilter;
use App\Geo\ManagedZoneProvider;
use App\Repository\CommitteeRepository;
use App\Scope\ScopeGeneratorResolver;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class AudienceFilterDenormalizer implements DenormalizerInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;

    private const AUDIENCE_FILTER_DENORMALIZER_ALREADY_CALLED = 'AUDIENCE_FILTER_DENORMALIZER_ALREADY_CALLED';

    public function __construct(
        private readonly ManagedZoneProvider $managedZoneProvider,
        private readonly Security $security,
        private readonly ScopeGeneratorResolver $scopeGeneratorResolver,
        private readonly CommitteeRepository $committeeRepository,
    ) {
    }

    public function denormalize($data, $type, $format = null, array $context = [])
    {
        $context[self::AUDIENCE_FILTER_DENORMALIZER_ALREADY_CALLED] = true;

        /** @var AudienceFilter $audienceFilter */
        $audienceFilter = $this->denormalizer->denormalize($data, $type, $format, $context);

        if ($scope = $this->scopeGeneratorResolver->generate()) {
            $audienceFilter->setZones($scope->getZones());
            if ($committeeUuids = $scope->getCommitteeUuids()) {
                $audienceFilter->setCommittee($this->committeeRepository->findOneByUuid(current($committeeUuids)));
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

    public function supportsDenormalization($data, $type, $format = null, array $context = [])
    {
        return
            empty($context[self::AUDIENCE_FILTER_DENORMALIZER_ALREADY_CALLED])
            && AudienceFilter::class === $type;
    }
}
