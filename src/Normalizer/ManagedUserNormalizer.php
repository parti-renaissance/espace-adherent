<?php

declare(strict_types=1);

namespace App\Normalizer;

use App\Api\Serializer\ManagedUserContextBuilder;
use App\Entity\Projection\ManagedUser;
use App\Repository\SubscriptionTypeRepository;
use App\Scope\ScopeGeneratorResolver;
use App\Subscription\SubscriptionTypeEnum;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class ManagedUserNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    private ?array $subscriptionTypesCache = null;

    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly ScopeGeneratorResolver $scopeGeneratorResolver,
        private readonly SubscriptionTypeRepository $subscriptionTypeRepository,
    ) {
    }

    /** @param ManagedUser $object */
    public function normalize($object, $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        $data = $this->normalizer->normalize($object, $format, $context + [__CLASS__ => true]);

        $groups = $context['groups'] ?? [];
        $isVox = \is_array($groups) && \in_array(ManagedUserContextBuilder::GROUP_VOX, $groups, true);

        if ($isVox) {
            $data['roles'] = $this->formatRolesVox($object);

            $isVoxDetail = \in_array(ManagedUserContextBuilder::GROUP_VOX_DETAIL, $groups, true);
            if ($isVoxDetail) {
                $data['subscription_types'] = $this->formatSubscriptionTypes($object);
            }
        } else {
            $data['email_subscription'] = $object->isEmailSubscribed();

            if ($scope = $this->scopeGeneratorResolver->generate()) {
                if ($scope->getMainUser()->isModemMembership()) {
                    $data['email'] = null;
                }

                if (!empty($subscriptionType = SubscriptionTypeEnum::SUBSCRIPTION_TYPES_BY_SCOPES[$scope->getMainCode()] ?? null)) {
                    $data['email_subscription'] = $data['email_subscription'] && \in_array($subscriptionType, $object->getSubscriptionTypes(), true);
                }
            }

            if (array_intersect(['managed_users_list', 'managed_user_read'], $groups)) {
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
                                        !empty($role['is_delegated']) ? (' délégué'.('female' === $object->getGender() ? 'e' : '')) : ''
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
        return $data instanceof ManagedUser && !isset($context[__CLASS__]);
    }

    private function formatRolesVox(ManagedUser $managedUser): array
    {
        $roles = [];

        foreach ($managedUser->getRolesAsArray() as $role) {
            $label = $this->translator->trans(
                $key = 'role.'.$role['role'],
                ['gender' => $managedUser->getGender()]
            );

            $roles[] = [
                'code' => $role['role'],
                'label' => $label === $key ? $role['role'] : $label,
                'is_delegated' => $role['is_delegated'] ?? false,
                'function' => $role['function'] ?? null,
            ];
        }

        return $roles;
    }

    private function formatSubscriptionTypes(ManagedUser $managedUser): array
    {
        $allTypes = $this->getAllSubscriptionTypes();
        $userCodes = $managedUser->getSubscriptionTypes();

        $result = [];
        foreach ($allTypes as $code => $label) {
            $result[] = [
                'code' => $code,
                'label' => $label,
                'checked' => \in_array($code, $userCodes, true),
            ];
        }

        return $result;
    }

    private function getAllSubscriptionTypes(): array
    {
        if (null === $this->subscriptionTypesCache) {
            $this->subscriptionTypesCache = [];
            foreach ($this->subscriptionTypeRepository->findAllOrderedByPosition() as $type) {
                $this->subscriptionTypesCache[$type->getCode()] = $type->getLabel();
            }
        }

        return $this->subscriptionTypesCache;
    }
}
