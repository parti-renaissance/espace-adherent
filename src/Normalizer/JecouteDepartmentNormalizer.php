<?php

namespace App\Normalizer;

use App\Entity\Geo\Department;
use App\Entity\Jecoute\Region;
use App\Repository\Jecoute\RegionRepository;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class JecouteDepartmentNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    private const ALREADY_CALLED = 'JECOUTE_DEPARTMENT_NORMALIZER_ALREADY_CALLED';

    private $regionRepository;

    public function __construct(RegionRepository $regionRepository)
    {
        $this->regionRepository = $regionRepository;
    }

    /**
     * @param Department $object
     */
    public function normalize($object, $format = null, array $context = [])
    {
        $context[self::ALREADY_CALLED] = true;

        $data = $this->normalizer->normalize($object, $format, $context);

        if (\in_array('department_read', $context['groups'])) {
            $region = $this->findRegion($object);

            $data['region'] = $this->normalizer->normalize($region, $format, array_merge_recursive($context, ['groups' => 'jecoute_region_read']));
        }

        return $data;
    }

    public function supportsNormalization($data, $format = null, array $context = [])
    {
        return !isset($context[self::ALREADY_CALLED]) && $data instanceof Department;
    }

    private function findRegion(Department $department): ?Region
    {
        return $this->regionRepository->findOneBy(['geoRegion' => $department->getRegion()]);
    }
}
