<?php

namespace App\Normalizer;

use App\Entity\Team\Member;
use App\Entity\Team\Team;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class TeamMembersNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    private const ALREADY_CALLED = 'TEAM_MEMBERS_NORMALIZER_ALREADY_CALLED';

    /**
     * @param Team $object
     *
     * @return array|\ArrayObject|bool|float|int|string|null
     */
    public function normalize($object, $format = null, array $context = [])
    {
        $context[self::ALREADY_CALLED] = true;

        $data = $this->normalizer->normalize($object, $format, $context);

        $data['members'] = array_map(function (Member $member) {
            return [
                'uuid' => $member->getUuid(),
                'first_name' => $member->getAdherent()->getFirstName(),
                'last_name' => $member->getAdherent()->getLastName(),
                'registred_at' => $member->getAdherent()->getRegisteredAt()->format('c'),
                'postal_code' => $member->getAdherent()->getPostalCode(),
            ];
        }, $object->getMembers()->toArray());

        return $data;
    }

    public function supportsNormalization($data, $format = null, array $context = [])
    {
        return
            !isset($context[self::ALREADY_CALLED])
            && $data instanceof Team
            && \in_array('team_read', $context['groups'] ?? [])
        ;
    }
}
