<?php

declare(strict_types=1);

namespace App\Normalizer;

use App\Adherent\MandateTypeEnum;
use App\Adherent\Tag\TagTranslator;
use App\Api\Serializer\ManagedUserContextBuilder;
use App\Entity\Projection\ManagedUser;
use App\Repository\SubscriptionTypeRepository;
use App\Scope\ScopeEnum;
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
        private readonly TagTranslator $tagTranslator,
        private readonly ScopeGeneratorResolver $scopeGeneratorResolver,
        private readonly SubscriptionTypeRepository $subscriptionTypeRepository,
    ) {
    }

    /** @param ManagedUser $object */
    public function normalize($object, $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        $data = $this->normalizer->normalize($object, $format, $context + [__CLASS__ => true]);
        $groups = $context['groups'] ?? [];

        if ($this->isVoxContext($groups)) {
            return $this->applyVoxContext($data, $object, $groups);
        }

        return $this->applyDefaultContext($data, $object);
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

    private function isVoxContext(mixed $groups): bool
    {
        return \is_array($groups) && \in_array(ManagedUserContextBuilder::GROUP_VOX, $groups, true);
    }

    private function applyVoxContext(array $data, ManagedUser $object, array $groups): array
    {
        $data['roles'] = $this->formatRolesVox($object);
        $data['elect_mandates'] = $this->formatMandates($object->getElectMandates());
        $data['adherent_tags'] = $this->translateTags($object->adherentTags);
        $data['static_tags'] = $this->translateTags($object->staticTags);
        $data['elect_tags'] = $this->translateTags($object->electTags);

        if (\in_array(ManagedUserContextBuilder::GROUP_VOX_DETAIL, $groups, true)) {
            $data['subscription_types'] = $this->formatSubscriptionTypes($object);
        }

        return $data;
    }

    private function applyDefaultContext(array $data, ManagedUser $object): array
    {
        $data['email_subscription'] = $object->isEmailSubscribed();
        $data = $this->applyScopeBusinessRules($data, $object);

        $data['tags'] ??= [];
        $data = $this->appendRoleTags($data, $object);
        $data = $this->appendMandateTags($data);

        return $data;
    }

    private function applyScopeBusinessRules(array $data, ManagedUser $object): array
    {
        $scope = $this->scopeGeneratorResolver->generate();
        if (!$scope) {
            return $data;
        }

        if ($scope->getMainUser()->isModemMembership()) {
            $data['email'] = null;
        }

        $subscriptionType = SubscriptionTypeEnum::SUBSCRIPTION_TYPES_BY_SCOPES[$scope->getMainCode()] ?? null;
        if (!empty($subscriptionType)) {
            $data['email_subscription'] = $data['email_subscription'] && \in_array($subscriptionType, $object->getSubscriptionTypes(), true);
        }

        return $data;
    }

    private function appendRoleTags(array $data, ManagedUser $object): array
    {
        if (!isset($data['roles'])) {
            return $data;
        }

        $sortedRoles = $this->sortRolesByPriority($data['roles']);
        foreach ($sortedRoles as $role) {
            $data['tags'][] = [
                'type' => 'role',
                'label' => $this->formatRoleLabel($role, $object->getGender()),
                'tooltip' => $role['function'] ?? null,
            ];
        }

        unset($data['roles']);

        return $data;
    }

    private function appendMandateTags(array $data): array
    {
        if (empty($data['elect_mandates'])) {
            return $data;
        }

        foreach ($this->sortMandatesByPriority($data['elect_mandates']) as $mandate) {
            $data['tags'][] = [
                'type' => 'mandate',
                'label' => $this->getTranslatedMandateLabel($mandate),
            ];
        }

        return $data;
    }

    private function formatRolesVox(ManagedUser $managedUser): array
    {
        $roles = [];
        $sortedRoles = $this->sortRolesByPriority($managedUser->getRoles());

        foreach ($sortedRoles as $role) {
            $code = $role['code'] ?? '';
            if ('' === $code) {
                continue;
            }

            $roles[] = [
                'code' => $code,
                'label' => $this->formatRoleLabel($role, $managedUser->getGender()),
                'is_delegated' => $role['is_delegated'] ?? false,
                'function' => $role['function'] ?? null,
                'zones' => $role['zones'] ?? null,
                'zone_codes' => $role['zone_codes'] ?? null,
                'delegator' => $this->formatDelegator($role),
            ];
        }

        return $roles;
    }

    private function formatDelegator(array $role): ?array
    {
        $delegator = $role['delegator'] ?? null;
        if (!$delegator) {
            return null;
        }

        $gender = $delegator['gender'] ?? null;
        $code = $role['code'] ?? '';
        $zoneLabels = $role['zone_labels'] ?? null;

        $roleLabel = $this->getTranslatedRoleLabel($code, $gender);
        if ($zoneLabels) {
            $roleLabel = \sprintf('%s (%s)', $roleLabel, $zoneLabels);
        }

        return [
            'first_name' => $delegator['first_name'] ?? null,
            'last_name' => $delegator['last_name'] ?? null,
            'role' => $roleLabel,
        ];
    }

    private function formatRoleLabel(array $role, ?string $gender): string
    {
        $code = $role['code'] ?? '';
        $isDelegated = !empty($role['is_delegated']);
        $function = $role['function'] ?? null;
        $zoneLabels = $role['zone_labels'] ?? null;

        if ($isDelegated && $function) {
            return $zoneLabels ? \sprintf('%s (%s)', $function, $zoneLabels) : $function;
        }

        $translatedLabel = $this->getTranslatedRoleLabel($code, $gender);

        if (!empty($zoneLabels)) {
            return \sprintf('%s (%s)', $translatedLabel, $zoneLabels);
        }

        return $translatedLabel;
    }

    private function formatMandates(?array $mandates): array
    {
        if (!$mandates) {
            return [];
        }

        return array_map(
            fn (string $code) => [
                'code' => $code,
                'label' => $this->getTranslatedMandateLabel($code),
            ],
            $this->sortMandatesByPriority($mandates)
        );
    }

    private function formatSubscriptionTypes(ManagedUser $managedUser): array
    {
        $userCodes = $managedUser->getSubscriptionTypes();

        $result = [];
        foreach ($this->getAllSubscriptionTypes() as $code => $label) {
            $result[] = [
                'code' => $code,
                'label' => $label,
                'subscribed' => \in_array($code, $userCodes, true),
            ];
        }

        return $result;
    }

    private function translateTags(?array $tags): ?array
    {
        if (!$tags) {
            return null;
        }

        return array_map(
            function (array $tag) {
                $code = $tag['code'] ?? '';

                return [
                    'code' => $code,
                    'label' => $code ? $this->tagTranslator->trans($code, false) : '',
                ];
            },
            $tags
        );
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

    private function sortRolesByPriority(array $roles): array
    {
        if (empty($roles)) {
            return [];
        }

        usort($roles, function (array $a, array $b): int {
            $aDelegated = !empty($a['is_delegated']);
            $bDelegated = !empty($b['is_delegated']);

            if ($aDelegated !== $bDelegated) {
                return $aDelegated ? 1 : -1;
            }

            $aIndex = ($idx = array_search($a['code'] ?? '', ScopeEnum::ALL, true)) === false ? \PHP_INT_MAX : $idx;
            $bIndex = ($idx = array_search($b['code'] ?? '', ScopeEnum::ALL, true)) === false ? \PHP_INT_MAX : $idx;

            return $aIndex <=> $bIndex;
        });

        return $roles;
    }

    private function sortMandatesByPriority(array $mandates): array
    {
        if (empty($mandates)) {
            return [];
        }

        usort($mandates, function (string $a, string $b): int {
            $aIndex = ($idx = array_search($a, MandateTypeEnum::ALL, true)) === false ? \PHP_INT_MAX : $idx;
            $bIndex = ($idx = array_search($b, MandateTypeEnum::ALL, true)) === false ? \PHP_INT_MAX : $idx;

            return $aIndex <=> $bIndex;
        });

        return $mandates;
    }
}
