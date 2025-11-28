<?php

declare(strict_types=1);

namespace App\Normalizer;

use App\Entity\Team\Member;
use App\Entity\Team\Team;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class TeamMembersNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    /**
     * @param Team $object
     */
    public function normalize($object, $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        $data = $this->normalizer->normalize($object, $format, $context + [__CLASS__ => true]);

        $data['members'] = array_map(function (Member $member) {
            return [
                'adherent_uuid' => $member->getAdherent()->getUuid(),
                'first_name' => $member->getAdherent()->getFirstName(),
                'last_name' => $member->getAdherent()->getLastName(),
                'registered_at' => $member->getAdherent()->getRegisteredAt()->format('c'),
                'postal_code' => $member->getAdherent()->getPostalCode(),
            ];
        }, $object->getMembers()->toArray());

        return $data;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            Team::class => false,
        ];
    }

    public function supportsNormalization($data, $format = null, array $context = []): bool
    {
        return
            !isset($context[__CLASS__])
            && $data instanceof Team
            && \in_array('team_read', $context['groups'] ?? []);
    }
}
