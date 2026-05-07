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
 * Sanity check : tout filtre publié par PublicationsFilterLayout (la nouvelle feature front)
 * doit avoir une propriété sérialisable correspondante sur AdherentMessageFilter.
 *
 * Sinon l'utilisateur soumet un filtre qui n'est jamais persisté ni appliqué
 * → bug silencieux côté UX, audience vide ou incohérente côté serveur.
 */
#[Group('unit')]
class PublicationsFilterLayoutBackingTest extends KernelTestCase
{
    private const TARGET_GROUP = 'adherent_message_update_filter';

    /**
     * Codes layout qui ne mappent pas 1:1 vers une propriété AdherentMessageFilter mais
     * sont gérés explicitement (ex: alias, groupes virtuels, codes calculés).
     */
    private const CODE_TO_PROPERTY_MAP = [
        'age' => 'ageMin',                 // setAge() dispatche vers ageMin/ageMax
        'first_membership' => 'firstMembershipSince', // setFirstMembership() dispatche
        'last_membership' => 'lastMembershipSince',   // setLastMembership() dispatche
        'registered' => 'registeredSince',            // setRegistered() dispatche
        'committee_uuids' => 'committee', // committeeUuids = alias dans certains scopes (cf. CommitteeFilterBuilder)
        'scope_targets' => 'scopeTargets',
    ];

    /**
     * Builders dont l'instanciation/build() requiert un Scope objet résolu via le runtime
     * (Security context). Inutilisables sans request → on les skip dans ce sanity test.
     * Leur correctness est couverte par les tests fonctionnels (ex: AdherentRepositoryScopeTargetTest).
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
     * @return iterable<string, string>  context => code
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
        // Couvre les scopes nationaux (qui débloquent isCertified et scopeTargets via certains layouts)
        // et un scope non-national pour vérifier le comportement transverse.
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
