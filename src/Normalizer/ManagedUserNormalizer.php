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
    private array $roleTranslationCache = [];

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

            return $data;
        }

        $data['email_subscription'] = $object->isEmailSubscribed();

        if ($scope = $this->scopeGeneratorResolver->generate()) {
            if ($scope->getMainUser()->isModemMembership()) {
                $data['email'] = null;
            }

            if (!empty($subscriptionType = SubscriptionTypeEnum::SUBSCRIPTION_TYPES_BY_SCOPES[$scope->getMainCode()] ?? null)) {
                $data['email_subscription'] = $data['email_subscription'] && \in_array($subscriptionType, $object->getSubscriptionTypes(), true);
            }
        }

        $gender = $object->getGender();

        if (isset($data['roles'])) {
            $delegatedSuffix = 'female' === $gender ? ' déléguée' : ' délégué';

            $data['tags'] = array_merge(
                $data['tags'] ?? [],
                array_map(
                    function (array $role) use ($gender, $delegatedSuffix) {
                        $code = $role['code'];

                        return [
                            'type' => 'role',
                            'label' => $this->getTranslatedRoleLabel($code, $gender).(!empty($role['is_delegated']) ? $delegatedSuffix : ''),
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
                    fn (string $mandate) => [
                        'type' => 'mandate',
                        'label' => $this->getTranslatedMandateLabel($mandate),
                    ],
                    $data['mandates']
                )
            );
        } elseif (!empty($data['declared_mandates'])) {
            $data['tags'] = array_merge(
                $data['tags'] ?? [],
                array_map(
                    fn (string $mandate) => [
                        'type' => 'declared_mandate',
                        'label' => $this->getTranslatedMandateLabel($mandate),
                    ],
                    $data['declared_mandates']
                )
            );
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
        $gender = $managedUser->getGender();

        foreach ($managedUser->getRoles() as $role) {
            $code = $role['code'] ?? '';
            if ('' === $code) {
                continue;
            }

            $roles[] = [
                'code' => $code,
                'label' => $this->getTranslatedRoleLabel($code, $gender),
                'is_delegated' => $role['is_delegated'] ?? false,
                'function' => $role['function'] ?? null,
                'zones' => $role['zones'] ?? null,
                'zone_codes' => $role['zone_codes'] ?? null,
            ];
        }

        return $roles;
    }

    private function getTranslatedRoleLabel(string $code, ?string $gender): string
    {
        $cacheKey = $code.'_'.$gender;

        if (!isset($this->roleTranslationCache[$cacheKey])) {
            $key = 'role.'.$code;
            $label = $this->translator->trans($key, ['gender' => $gender]);
            $this->roleTranslationCache[$cacheKey] = $label === $key ? $code : $label;
        }

        return $this->roleTranslationCache[$cacheKey];
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
                'subscribed' => \in_array($code, $userCodes, true),
            ];
        }

        return $result;
    }

    private function getAllSubscriptionTypes(): array
    {
        if (null === $this->subscriptionTypesCache) {
            $this->subscriptionTypesCache = [];
            foreach ($this->subscriptionTypeRepository->findAllOrderedByPosition(SubscriptionTypeEnum::DEFAULT_EMAIL_TYPES) as $type) {
                $this->subscriptionTypesCache[$type->getCode()] = $type->getLabel();
            }
        }

        return $this->subscriptionTypesCache;
    }

    private function getTranslatedMandateLabel(string $mandate): string
    {
        $cacheKey = 'mandate_'.$mandate;

        if (!isset($this->roleTranslationCache[$cacheKey])) {
            $key = 'adherent.mandate.type.'.$mandate;
            $label = $this->translator->trans($key);
            $this->roleTranslationCache[$cacheKey] = $label === $key ? $mandate : $label;
        }

        return $this->roleTranslationCache[$cacheKey];
    }
}
