<?php

namespace App\Normalizer;

use App\Entity\Projection\ManagedUser;
use App\Scope\ScopeGeneratorResolver;
use App\Subscription\SubscriptionTypeEnum;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class ManagedUserNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    private const MANAGED_USER_NORMALIZER_ALREADY_CALLED = 'MANAGED_USER_NORMALIZER_ALREADY_CALLED';

    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly ScopeGeneratorResolver $scopeGeneratorResolver,
        private readonly Security $security,
    ) {
    }

    /** @param ManagedUser $object */
    public function normalize($object, $format = null, array $context = [])
    {
        $context[self::MANAGED_USER_NORMALIZER_ALREADY_CALLED] = true;

        $data = $this->normalizer->normalize($object, $format, $context);

        $data['email_subscription'] = null;

        $scopeGenerator = $this->scopeGeneratorResolver->resolve();

        if ($this->security->getUser()->modemMembership) {
            $data['email'] = null;
        }

        if ($scopeGenerator && !empty($subscriptionType = SubscriptionTypeEnum::SUBSCRIPTION_TYPES_BY_SCOPES[$scopeGenerator->getCode()] ?? null)) {
            $data['email_subscription'] = \in_array($subscriptionType, $object->getSubscriptionTypes(), true);
        }

        if (array_intersect(['managed_users_list', 'managed_user_read'], $context['groups'] ?? [])) {
            if (isset($data['roles'])) {
                $data['tags'] = array_merge(
                    $data['tags'] ?? [],
                    array_map(
                        function (array $role) {
                            return [
                                'type' => 'role',
                                'label' => ($label = $this->translator->trans($key = 'role.'.$role['role'])) === $key ? $role['role'] : $label,
                                'tooltip' => $role['function'] ?? null,
                            ];
                        },
                        $data['roles']
                    )
                );
                unset($data['roles']);
            }

            if (!empty($data['mandates'])) {
                $data['tags'] = array_merge(
                    $data['tags'] ?? [],
                    array_map(
                        function (string $mandate) {
                            return [
                                'type' => 'mandate',
                                'label' => ($label = $this->translator->trans($key = 'adherent.mandate.type.'.$mandate)) === $key ? $mandate : $label,
                            ];
                        },
                        $data['mandates']
                    )
                );
            } elseif (!empty($data['declared_mandates'])) {
                $data['tags'] = array_merge(
                    $data['tags'] ?? [],
                    array_map(
                        function (string $mandate) {
                            return [
                                'type' => 'declared_mandate',
                                'label' => ($label = $this->translator->trans($key = 'adherent.mandate.type.'.$mandate)) === $key ? $mandate : $label,
                            ];
                        },
                        $data['declared_mandates']
                    )
                );
            }
        }

        return $data;
    }

    public function supportsNormalization($data, $format = null, array $context = []): bool
    {
        return empty($context[self::MANAGED_USER_NORMALIZER_ALREADY_CALLED]) && $data instanceof ManagedUser;
    }
}
