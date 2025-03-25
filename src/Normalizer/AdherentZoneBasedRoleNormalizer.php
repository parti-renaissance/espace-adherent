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

    public function __construct(private readonly TranslatorInterface $translator)
    {
    }

    public function normalize($object, ?string $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        /** @var Adherent $object */
        $data = $this->normalizer->normalize($object, $format, $context + [__CLASS__ => true]);

        $roles = array_map(fn (AdherentZoneBasedRole $role) => $this->translator->trans('role.'.$role->getType()), $object->getZoneBasedRoles());
        $data['roles'] = implode(',', $roles);

        return $data;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            Adherent::class => false,
        ];
    }

    public function supportsNormalization($data, $format = null, array $context = []): bool
    {
        return
            !isset($context[__CLASS__])
            && $data instanceof Adherent
            && \in_array('national_event_inscription:webhook', $context['groups'] ?? []);
    }
}
