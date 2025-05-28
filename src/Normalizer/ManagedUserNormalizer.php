<?php

namespace App\Normalizer;

use App\Entity\Projection\ManagedUser;
use App\Scope\ScopeGeneratorResolver;
use App\Subscription\SubscriptionTypeEnum;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class ManagedUserNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly ScopeGeneratorResolver $scopeGeneratorResolver,
        private readonly Security $security,
    ) {
    }

    /** @param ManagedUser $object */
    public function normalize($object, $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        $data = $this->normalizer->normalize($object, $format, $context + [__CLASS__ => true]);

        $data['email_subscription'] = $object->isEmailSubscribed();

        if ($scope = $this->scopeGeneratorResolver->generate()) {
            if ($scope->getMainUser()->isModemMembership()) {
                $data['email'] = null;
            }

            if (!empty($subscriptionType = SubscriptionTypeEnum::SUBSCRIPTION_TYPES_BY_SCOPES[$scope->getMainCode()] ?? null)) {
                $data['email_subscription'] = $data['email_subscription'] && \in_array($subscriptionType, $object->getSubscriptionTypes(), true);
            }
        }

        if (array_intersect(['managed_users_list', 'managed_user_read'], $context['groups'] ?? [])) {
            if (isset($data['roles'])) {
                $data['tags'] = array_merge(
                    $data['tags'] ?? [],
                    array_map(
                        function (array $role) use ($object) {
                            return [
                                'type' => 'role',
                                'label' => \sprintf(
                                    '%s%s',
                                    ($label = $this->translator->trans($key = 'role.'.$role['role'], ['gender' => $object->getGender()])) === $key ? $role['role'] : $label,
                                    !empty($role['is_delegated']) ? (' délégué' . (('female' === $object->getGender() ? 'e' : ''))) : ''
                                ),
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

    public function getSupportedTypes(?string $format): array
    {
        return [
            ManagedUser::class => false,
        ];
    }

    public function supportsNormalization($data, $format = null, array $context = []): bool
    {
        return !isset($context[__CLASS__]) && $data instanceof ManagedUser;
    }
}
