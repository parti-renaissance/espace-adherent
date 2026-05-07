<?php

declare(strict_types=1);

namespace Tests\App\Unit\Repository;

use App\Entity\AdherentMessage\AdherentMessageFilter;
use App\JMEFilter\FilterBuilder\CommitteeFilterBuilder;
use App\JMEFilter\FilterBuilder\ScopeTargetFilterBuilder;
use App\JMEFilter\FilterBuilder\ZoneAutocompleteFilterBuilder;
use App\JMEFilter\Layout\PublicationsFilterLayout;
use App\Scope\FeatureEnum;
use App\Scope\ScopeEnum;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Serializer\Attribute\Groups;

/**
 * Sanity check: every filter published by PublicationsFilterLayout (the new front feature)
 * must have a matching serializable property on AdherentMessageFilter.
 *
 * Otherwise the user submits a filter that is never persisted nor applied
 * → silent UX bug, empty or inconsistent audience server-side.
 */
#[Group('unit')]
class PublicationsFilterLayoutBackingTest extends KernelTestCase
{
    private const TARGET_GROUP = 'adherent_message_update_filter';

    /**
     * Layout codes that do not map 1:1 to an AdherentMessageFilter property but
     * are handled explicitly (e.g. aliases, virtual groups, computed codes).
     */
    private const CODE_TO_PROPERTY_MAP = [
        'age' => 'ageMin',                 // setAge() dispatches to ageMin/ageMax
        'first_membership' => 'firstMembershipSince', // setFirstMembership() dispatches
        'last_membership' => 'lastMembershipSince',   // setLastMembership() dispatches
        'registered' => 'registeredSince',            // setRegistered() dispatches
        'committee_uuids' => 'committee', // committeeUuids = alias used in some scopes (cf. CommitteeFilterBuilder)
        'scope_targets' => 'scopeTargets',
    ];

    /**
     * Builders whose instantiation/build() requires a Scope object resolved at runtime
     * (Security context). Unusable without a request → skipped in this sanity test.
     * Their correctness is covered by functional tests (e.g. AdherentRepositoryScopeTargetTest).
     */
    private const SKIPPED_BUILDERS = [
        CommitteeFilterBuilder::class,
        ZoneAutocompleteFilterBuilder::class,
        ScopeTargetFilterBuilder::class,
    ];

    public function testEachPublicationsFilterCodeHasBackingDtoProperty(): void
    {
        $layout = static::getContainer()->get(PublicationsFilterLayout::class);
        $reflection = new \ReflectionClass(AdherentMessageFilter::class);

        $writableProperties = [];
        foreach ($reflection->getProperties() as $property) {
            if (self::propertyHasGroup($property, self::TARGET_GROUP)) {
                $writableProperties[$property->getName()] = true;
            }
        }

        $missing = [];

        foreach ($this->collectAllPublishedCodes($layout) as $context => $code) {
            $candidates = [
                self::snakeToCamel($code),
                $code,
                self::CODE_TO_PROPERTY_MAP[$code] ?? null,
            ];

            $matched = false;
            foreach (array_filter($candidates) as $candidate) {
                if (isset($writableProperties[$candidate])) {
                    $matched = true;
                    break;
                }
            }

            if (!$matched) {
                $missing[] = \sprintf('%s → code "%s"', $context, $code);
            }
        }

        self::assertSame(
            [],
            $missing,
            \sprintf(
                "PublicationsFilterLayout publishes filter codes that have no backing #[Groups('%s')] property on AdherentMessageFilter:\n - %s",
                self::TARGET_GROUP,
                implode("\n - ", $missing),
            ),
        );
    }

    /**
     * @return iterable<string, string> context => code
     */
    private function collectAllPublishedCodes(PublicationsFilterLayout $layout): iterable
    {
        $container = static::getContainer();

        foreach (self::scopesToTest() as $scope) {
            foreach ($layout->getGroupConfigs($scope) as $groupConfig) {
                foreach ($groupConfig->filters as $filterConfig) {
                    if (\in_array($filterConfig->builderClass, self::SKIPPED_BUILDERS, true)) {
                        continue;
                    }

                    if (!$container->has($filterConfig->builderClass)) {
                        continue;
                    }

                    $builder = $container->get($filterConfig->builderClass);
                    foreach ($builder->build($scope, FeatureEnum::PUBLICATIONS, false) as $filter) {
                        yield \sprintf('scope=%s, builder=%s', $scope, $filterConfig->builderClass) => $filter->getCode();
                    }
                }
            }
        }
    }

    /**
     * @return list<string>
     */
    private static function scopesToTest(): array
    {
        // Covers national scopes (which unlock isCertified and scopeTargets in some layouts)
        // and one non-national scope to verify the cross-cutting behavior.
        return [
            ScopeEnum::NATIONAL,
            ScopeEnum::PRESIDENT_DEPARTMENTAL_ASSEMBLY,
        ];
    }

    private static function snakeToCamel(string $code): string
    {
        $parts = explode('_', $code);

        return $parts[0].implode('', array_map(ucfirst(...), \array_slice($parts, 1)));
    }

    private static function propertyHasGroup(\ReflectionProperty $property, string $groupName): bool
    {
        foreach ($property->getAttributes(Groups::class) as $attribute) {
            $args = $attribute->getArguments();
            $groups = isset($args[0]) ? (array) $args[0] : [];

            if (\in_array($groupName, $groups, true)) {
                return true;
            }
        }

        return false;
    }
}
