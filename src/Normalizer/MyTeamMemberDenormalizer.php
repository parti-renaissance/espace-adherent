<?php

namespace App\Normalizer;

use App\Entity\MyTeam\Member;
use App\Scope\FeatureEnum;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class MyTeamMemberDenormalizer implements DenormalizerInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;

    private const DENORMALIZER_ALREADY_CALLED = 'MY_TEAM_MEMBER_DENORMALIZER_ALREADY_CALLED';

    public function denormalize($data, $type, $format = null, array $context = [])
    {
        $context[self::DENORMALIZER_ALREADY_CALLED] = true;

        $data['scope_features'] = array_unique(array_merge(
            $data['scope_features'], FeatureEnum::DELEGATED_ACCESSES_BY_DEFAULT
        ));

        return $this->denormalizer->denormalize($data, $type, $format, $context);
    }

    public function supportsDenormalization($data, $type, $format = null, array $context = [])
    {
        return
            empty($context[self::DENORMALIZER_ALREADY_CALLED])
            && Member::class === $type;
    }
}
