<?php

declare(strict_types=1);

namespace App\Normalizer;

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
        $isVox = \is_array($groups) && \in_array(ManagedUserContextBuilder::GROUP_VOX, $groups, true);

        if ($isVox) {
            $data['roles'] = $this->formatRolesVox($object);
            $data['elect_mandates'] = $this->formatMandates($object->getElectMandates());
            $data['adherent_tags'] = $this->translateTags($object->adherentTags);
            $data['static_tags'] = $this->translateTags($object->staticTags);
            $data['elect_tags'] = $this->translateTags($object->electTags);

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
        $committee = $object->getCommittee();
        $agora = $object->getAgora();

        if (isset($data['roles'])) {
            $data['tags'] = array_merge(
                $data['tags'] ?? [],
                array_map(
                    function (array $role) use ($gender, $committee, $agora) {
                        return [
                            'type' => 'role',
                            'label' => $this->formatRoleLabel($role, $gender, $committee, $agora),
                            'tooltip' => $role['function'] ?? null,
                        ];
                    },
                    $data['roles']
                )
            );
            unset($data['roles']);
        }

        if (!empty($data['elect_mandates'])) {
            $data['tags'] = array_merge(
                $data['tags'] ?? [],
                array_map(
                    fn (string $mandate) => [
                        'type' => 'mandate',
                        'label' => $this->getTranslatedMandateLabel($mandate),
                    ],
                    $data['elect_mandates']
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
        $committee = $managedUser->getCommittee();
        $agora = $managedUser->getAgora();

        foreach ($managedUser->getRoles() as $role) {
            $code = $role['code'] ?? '';
            if ('' === $code) {
                continue;
            }

            $roles[] = [
                'code' => $code,
                'label' => $this->formatRoleLabel($role, $gender, $committee, $agora),
                'is_delegated' => $role['is_delegated'] ?? false,
                'function' => $role['function'] ?? null,
                'zones' => $role['zones'] ?? null,
                'zone_codes' => $role['zone_codes'] ?? null,
            ];
        }

        return $roles;
    }

    /**
     * Formats the role label according to priority rules:
     * 1. Delegated: "{function} ({zone_codes})" or "{function}" if no zone
     * 2. National: "{translated_role}" (no zone)
     * 3. Animator: "{translated_role} ({committee})"
     * 4. Agora (president/secretary): "{translated_role} ({agora})"
     * 5. With zone: "{translated_role} ({zone_codes})"
     * 6. Fallback: "{translated_role}"
     */
    private function formatRoleLabel(
        array $role,
        ?string $gender,
        ?string $committee,
        ?string $agora,
    ): string {
        $code = $role['code'] ?? '';
        $isDelegated = !empty($role['is_delegated']);
        $function = $role['function'] ?? null;
        $zoneCodes = $role['zone_codes'] ?? null;

        // Priority 1: Delegated role
        if ($isDelegated && $function) {
            if (!empty($zoneCodes)) {
                return \sprintf('%s (%s)', $function, $zoneCodes);
            }

            return $function;
        }

        $translatedLabel = $this->getTranslatedRoleLabel($code, $gender);

        // Priority 2: National role (no zone)
        if (ScopeEnum::isNational($code)) {
            return $translatedLabel;
        }

        // Priority 3: Animator with committee
        if (ScopeEnum::ANIMATOR === $code && !empty($committee)) {
            return \sprintf('%s (%s)', $translatedLabel, $committee);
        }

        // Priority 4: Agora roles
        if (\in_array($code, [ScopeEnum::AGORA_PRESIDENT, ScopeEnum::AGORA_GENERAL_SECRETARY], true) && !empty($agora)) {
            return \sprintf('%s (%s)', $translatedLabel, $agora);
        }

        // Priority 5: Role with zone
        if (!empty($zoneCodes)) {
            return \sprintf('%s (%s)', $translatedLabel, $zoneCodes);
        }

        // Priority 6: Fallback (label only)
        return $translatedLabel;
    }

    private function formatMandates(?array $mandates): array
    {
        if (!$mandates) {
            return [];
        }

        return array_map(
            function (string $code) {
                return [
                    'code' => $code,
                    'label' => $this->getTranslatedMandateLabel($code),
                ];
            },
            $mandates
        );
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
