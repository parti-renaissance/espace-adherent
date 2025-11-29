<?php

declare(strict_types=1);

namespace App\Normalizer;

use App\Entity\Geo\Region;
use App\Entity\Geo\Zone;
use App\Entity\Jecoute\Region as RegionCampaign;
use App\Repository\Geo\ZoneRepository;
use App\Repository\Jecoute\RegionRepository;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class RegionNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    public function __construct(
        private readonly RegionRepository $regionCampaignRepository,
        private readonly ZoneRepository $zoneRepository,
    ) {
    }

    /**
     * @param Region $object
     */
    public function normalize($object, $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        $data = $this->normalizer->normalize($object, $format, $context + [__CLASS__ => true]);

        $regionCampaignData = null;

        if (\in_array('jecoute_department_read', $context['groups'] ?? []) && isset($context['postal_code'])) {
            if ($regionCampaign = $this->findRegionCampaign($object, $context['postal_code'])) {
                $regionCampaignData = $this->normalizer->normalize($regionCampaign, $format, ['groups' => ['jecoute_region_read']]);
            }
        }

        $data['campaign'] = $regionCampaignData;

        return $data;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            Region::class => false,
        ];
    }

    public function supportsNormalization($data, $format = null, array $context = []): bool
    {
        return !isset($context[__CLASS__]) && $data instanceof Region;
    }

    private function findRegionCampaign(Region $region, string $postalCode): ?RegionCampaign
    {
        $department = null;
        $zone = $this->zoneRepository->findOneByPostalCode($postalCode);
        $geoZoneRegion = $this->zoneRepository->findGeoZoneByGeoRegion($region);

        if (null !== $zone) {
            $department = current($zone->getParentsOfType(Zone::DEPARTMENT));
        }

        if ($department) {
            return $this->regionCampaignRepository->findOneCampaignByZone($geoZoneRegion, $department, $postalCode);
        }

        return $this->regionCampaignRepository->findOneBy(['zone' => $geoZoneRegion]);
    }
}
