<?php

declare(strict_types=1);

namespace Tests\App\Unit\Repository;

use App\Adherent\MandateTypeEnum;
use App\Donation\DonatorStatusEnum;
use App\Entity\Adherent;
use App\Entity\AdherentMessage\AdherentMessage;
use App\Entity\AdherentMessage\AdherentMessageFilter;
use App\Entity\Committee;
use App\Entity\Geo\Zone;
use App\Repository\AdherentRepository;
use App\Scope\ScopeEnum;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Serializer\Attribute\Groups;

/**
 * Garde-fou future-proof : toute propriété de `AdherentMessageFilter` portant
 * `#[Groups('adherent_message_update_filter')]` (donc écrivable via API) DOIT être
 * appliquée par `AdherentRepository::buildAudienceQueryPieces`, OU figurer
 * dans EXPLICITLY_IGNORED_FILTERS avec une justification écrite.
 *
 * Si un nouveau filtre est ajouté au DTO sans branchement QB ni whitelist,
 * la CI crashera ici.
 */
#[Group('unit')]
class AdherentMessageFilterCoverageTest extends KernelTestCase
{
    private const TARGET_GROUP = 'adherent_message_update_filter';

    /**
     * Pour chaque propriété écrivable, le fragment SQL distinctif que doit produire
     * `buildAudienceQueryPieces` quand cette propriété est définie.
     *
     * Le test vérifie la présence de ce fragment dans la concaténation des clauses
     * WHERE / FROM / WITH générées par la méthode.
     */
    private const FILTER_TO_SQL_FRAGMENT = [
        'scope' => 'st_id',
        'gender' => 'a.gender',
        'ageMin' => 'a.birthdate <=',
        'ageMax' => 'a.birthdate >=',
        'firstName' => 'a.first_name',
        'lastName' => 'a.last_name',
        'registeredSince' => 'a.registered_at >=',
        'registeredUntil' => 'a.registered_at <=',
        'firstMembershipSince' => 'a.first_membership_donation >=',
        'firstMembershipBefore' => 'a.first_membership_donation <=',
        'lastMembershipSince' => 'a.last_membership_donation >=',
        'lastMembershipBefore' => 'a.last_membership_donation <=',
        'committee' => 'cm.committee_id',
        'isCertified' => 'a.certified_at',
        'zone' => 'az_filter',
        'isCommitteeMember' => 'cm.id',
        'electMandate' => 'adherent_mandate',
        'declaredMandate' => 'declared_mandate',
        'donatorStatus' => 'donator_current_year',
        'adherentTags' => 'tag_prefix',
        'electTags' => 'tag_contains_',
        'staticTags' => 'tag_contains_',
    ];

    /**
     * Filtres écrivables via API mais légitimement non appliqués par buildAudienceQueryPieces.
     * Toute entrée doit être justifiée par écrit (clé = nom de propriété, valeur = raison).
     */
    private const EXPLICITLY_IGNORED_FILTERS = [
        'uuid' => 'Identifiant technique du filtre (EntityIdentityTrait) — pas un critère de filtrage.',
        'audienceType' => 'Consommé hors buildAudienceQueryPieces par LegislativeCandidateMailchimpCampaignHandler (scope legislative_candidate uniquement)',
        'isCampusRegistered' => 'Résidu legacy — non exposé par PublicationsFilterLayout (nouvelle feature). Encore lu si valeur historique en BDD via CampusRegistrationConditionBuilder Mailchimp.',
        'segment' => 'Référence un AudienceSegment qui définit ses propres filtres — pas un filtre direct du QB.',
        'scopeTargets' => 'Logique CTE complexe — couvert par AdherentRepositoryScopeTargetTest existant.',
    ];

    public static function provideWritableFilterProperties(): iterable
    {
        $reflection = new \ReflectionClass(AdherentMessageFilter::class);

        foreach ($reflection->getProperties() as $property) {
            if (!self::propertyHasGroup($property, self::TARGET_GROUP)) {
                continue;
            }

            yield $property->getName() => [$property->getName()];
        }
    }

    #[DataProvider('provideWritableFilterProperties')]
    public function testEachWritableFilterPropertyIsAppliedOrExplicitlyIgnored(string $propertyName): void
    {
        if (\array_key_exists($propertyName, self::EXPLICITLY_IGNORED_FILTERS)) {
            self::markTestSkipped(\sprintf(
                'Property "%s" is explicitly ignored: %s',
                $propertyName,
                self::EXPLICITLY_IGNORED_FILTERS[$propertyName],
            ));
        }

        self::assertArrayHasKey(
            $propertyName,
            self::FILTER_TO_SQL_FRAGMENT,
            \sprintf(
                'Property "%s" is writable via API (#[Groups(\'%s\')]) but is neither mapped in FILTER_TO_SQL_FRAGMENT nor present in EXPLICITLY_IGNORED_FILTERS. Brancher le filtre dans AdherentRepository::buildAudienceQueryPieces ou whitelister avec justification.',
                $propertyName,
                self::TARGET_GROUP,
            ),
        );

        $expectedFragment = self::FILTER_TO_SQL_FRAGMENT[$propertyName];
        $sqlBlob = $this->buildSqlBlobForFilterProperty($propertyName);

        self::assertStringContainsString(
            $expectedFragment,
            $sqlBlob,
            \sprintf('Property "%s" did not produce the expected SQL fragment "%s" in buildAudienceQueryPieces output.', $propertyName, $expectedFragment),
        );
    }

    private function buildSqlBlobForFilterProperty(string $propertyName): string
    {
        $filter = new AdherentMessageFilter();
        $this->assignDummyValue($filter, $propertyName);

        $author = new Adherent();
        $message = AdherentMessage::createFromAdherent($author);
        $message->setInstanceScope(ScopeEnum::NATIONAL);
        $message->setFilter($filter);

        $repository = static::getContainer()->get(AdherentRepository::class);

        $reflection = new \ReflectionMethod($repository, 'buildAudienceQueryPieces');
        $pieces = $reflection->invoke($repository, $message, true, null);

        self::assertNotNull($pieces, \sprintf('buildAudienceQueryPieces returned null for property "%s".', $propertyName));

        return implode(' ', [
            $pieces['with'] ?? '',
            $pieces['baseFrom'] ?? '',
            implode(' AND ', $pieces['baseWhere'] ?? []),
            $pieces['branchEmailSql'] ?? '',
        ]);
    }

    private function assignDummyValue(AdherentMessageFilter $filter, string $propertyName): void
    {
        match ($propertyName) {
            'gender' => $filter->setGender('male'),
            'ageMin' => $filter->setAgeMin(18),
            'ageMax' => $filter->setAgeMax(99),
            'firstName' => $filter->setFirstName('Dummy'),
            'lastName' => $filter->setLastName('Dummy'),
            'registeredSince' => $filter->setRegisteredSince(new \DateTime('2020-01-01')),
            'registeredUntil' => $filter->setRegisteredUntil(new \DateTime('2024-12-31')),
            'firstMembershipSince' => $filter->firstMembershipSince = new \DateTime('2020-01-01'),
            'firstMembershipBefore' => $filter->firstMembershipBefore = new \DateTime('2024-12-31'),
            'lastMembershipSince' => $filter->setLastMembershipSince(new \DateTime('2020-01-01')),
            'lastMembershipBefore' => $filter->setLastMembershipBefore(new \DateTime('2024-12-31')),
            'committee' => $filter->setCommittee($this->buildCommitteeStub()),
            'isCertified' => $filter->setIsCertified(true),
            'zone' => $filter->setZone($this->buildZoneStub()),
            'isCommitteeMember' => $filter->setIsCommitteeMember(true),
            'electMandate' => $filter->setElectMandate(MandateTypeEnum::CONSEILLER_MUNICIPAL),
            'declaredMandate' => $filter->setDeclaredMandate('maire'),
            'donatorStatus' => $filter->setDonatorStatus(DonatorStatusEnum::DONATOR_N),
            'adherentTags' => $filter->adherentTags = 'adherent:foo',
            'electTags' => $filter->electTags = 'elect:foo',
            'staticTags' => $filter->staticTags = 'static:foo',
            'scope' => $filter->setScope(ScopeEnum::DEPUTY),
            default => self::fail(\sprintf('No dummy value mapping for property "%s". Add it to assignDummyValue() or whitelist the property.', $propertyName)),
        };
    }

    private function buildCommitteeStub(): Committee
    {
        $committee = $this->createMock(Committee::class);
        $committee->method('getId')->willReturn(1);

        return $committee;
    }

    private function buildZoneStub(): Zone
    {
        $zone = $this->createMock(Zone::class);
        $zone->method('getId')->willReturn(1);

        return $zone;
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
