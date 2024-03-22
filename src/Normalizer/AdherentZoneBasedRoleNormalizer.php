<?php

namespace App\Normalizer;

use App\Entity\Adherent;
use App\Entity\AdherentZoneBasedRole;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class AdherentZoneBasedRoleNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    private const ALREADY_CALLED = 'ADHERENT_ZONE_BASED_ROLE_NORMALIZER_ALREADY_CALLED';

    public function __construct(private readonly TranslatorInterface $translator)
    {
    }

    public function normalize($object, ?string $format = null, array $context = [])
    {
        $context[self::ALREADY_CALLED] = true;

        /** @var Adherent $object */
        $data = $this->normalizer->normalize($object, $format, $context);

        $roles = array_map(fn (AdherentZoneBasedRole $role) => $this->translator->trans('role.'.$role->getType()), $object->getZoneBasedRoles());
        $data['roles'] = implode(',', $roles);

        return $data;
    }

    public function supportsNormalization($data, $format = null, array $context = []): bool
    {
        return
            empty($context[self::ALREADY_CALLED])
            && $data instanceof Adherent
            && \in_array('national_event_inscription:webhook', $context['groups'] ?? []);
    }
}
