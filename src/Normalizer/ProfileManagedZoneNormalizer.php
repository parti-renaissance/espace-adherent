<?php

declare(strict_types=1);

namespace App\Normalizer;

use App\Entity\Adherent;
use App\OAuth\Model\Scope;
use App\Scope\ScopeEnum;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ProfileManagedZoneNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    public const GROUP = 'profile_managed_zone';

    public function __construct(private readonly AuthorizationCheckerInterface $authorizationChecker)
    {
    }

    /**
     * @param Adherent $object
     */
    public function normalize($object, $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        $data = $this->normalizer->normalize($object, $format, $context + [__CLASS__ => true]);
        $data['managed_zone'] = $managedZone = null;

        if ($this->authorizationChecker->isGranted(Scope::generateRole(Scope::SCOPE_PAD))) {
            $role = $object->findZoneBasedRole(ScopeEnum::PRESIDENT_DEPARTMENTAL_ASSEMBLY);

            if (!$role) {
                $delegatedAccesses = $object->getReceivedDelegatedAccessOfType(ScopeEnum::PRESIDENT_DEPARTMENTAL_ASSEMBLY);
                if ($delegatedAccesses->isEmpty()) {
                    return $data;
                }

                $role = $delegatedAccesses->first()->getDelegator()->findZoneBasedRole(ScopeEnum::PRESIDENT_DEPARTMENTAL_ASSEMBLY);
            }

            $managedZone = [
                'type' => 'departement',
                'code' => preg_replace('/[^0-9].*$/', '', $role->getZonesCodes()[0] ?? ''),
            ];
        } elseif ($this->authorizationChecker->isGranted(Scope::generateRole(Scope::SCOPE_MUNICIPAL_CANDIDATE))) {
            $role = $object->findZoneBasedRole(ScopeEnum::MUNICIPAL_CANDIDATE);

            if (!$role) {
                $delegatedAccesses = $object->getReceivedDelegatedAccessOfType(ScopeEnum::MUNICIPAL_CANDIDATE);
                if ($delegatedAccesses->isEmpty()) {
                    return $data;
                }

                $role = $delegatedAccesses->first()->getDelegator()->findZoneBasedRole(ScopeEnum::MUNICIPAL_CANDIDATE);
            }

            $managedZone = [
                'type' => 'commune',
                'code' => $role->getZonesCodes()[0],
            ];
        }

        $data['managed_zone'] = $managedZone;

        return $data;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [Adherent::class => false];
    }

    public function supportsNormalization($data, $format = null, array $context = []): bool
    {
        return
            \in_array(self::GROUP, $context['groups'] ?? [], true)
            && !isset($context[__CLASS__])
            && $data instanceof Adherent;
    }
}
