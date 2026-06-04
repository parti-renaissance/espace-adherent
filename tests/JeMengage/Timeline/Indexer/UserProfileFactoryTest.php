<?php

declare(strict_types=1);

namespace Tests\App\JeMengage\Timeline\Indexer;

use App\Entity\Adherent;
use App\Entity\AdherentMandate\ElectedRepresentativeAdherentMandate;
use App\Entity\Agora;
use App\Entity\AgoraMembership;
use App\Entity\Committee;
use App\Entity\CommitteeMembership;
use App\Entity\Geo\Zone;
use App\JeMengage\Timeline\Indexer\UserProfileFactory;
use App\JeMengage\Timeline\UserScopeTargetResolver;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

final class UserProfileFactoryTest extends TestCase
{
    private const string USER_UUID = 'cccccccc-3333-4333-8333-cccccccccccc';
    private const string COMMITTEE_UUID = 'aaaaaaaa-1111-4111-8111-aaaaaaaaaaaa';
    private const string AGORA_UUID = 'bbbbbbbb-2222-4222-8222-bbbbbbbbbbbb';

    public function testCreateMapsEnrichedProfileAndOmitsNullCivilityAgeAndDates(): void
    {
        $user = $this->createMock(Adherent::class);
        $user->method('getId')->willReturn(42);
        $user->tags = ['jeune', 'militant'];
        $user->method('getDeepZones')->willReturn([
            $this->zone('region', '11'),
            $this->zone('city', '75056'),
        ]);
        $user->agoraMemberships = new ArrayCollection([$this->agoraMembership(self::AGORA_UUID)]);
        $user->expects(self::atLeastOnce())->method('findElectedRepresentativeMandates')->with(true)->willReturn([
            $this->mandate('departmental_councilor'),
        ]);
        $user->method('getMandates')->willReturn(['depute']);
        $user->method('getCommitteeMembership')->willReturn($this->committeeMembership(self::COMMITTEE_UUID));
        $user->method('getGender')->willReturn(null);
        $user->method('getAge')->willReturn(null);
        $user->method('getFirstMembershipDonation')->willReturn(new \DateTime('2024-01-15 09:00:00', new \DateTimeZone('Europe/Paris')));
        $user->method('getLastMembershipDonation')->willReturn(null);
        $user->method('getRegisteredAt')->willReturn(new \DateTime('2023-11-01 00:00:00', new \DateTimeZone('UTC')));

        $body = $this->factory($user, ['national'])->create($user)->jsonSerialize();

        self::assertSame(42, $body['user_id']);
        self::assertSame(['jeune', 'militant'], $body['tags']);
        self::assertSame(['region:11', 'city:75056'], $body['zones']);
        self::assertSame([self::COMMITTEE_UUID], $body['committees']);
        self::assertSame([self::AGORA_UUID], $body['agoras']);
        self::assertSame(['departmental_councilor'], $body['mandate_types']);
        self::assertSame(['depute'], $body['declared_mandates']);
        self::assertSame(1, $body['committee_member']);
        self::assertSame(['national'], $body['scope_targets']);
        // Europe/Paris 09:00 in January (UTC+1) normalises to 08:00Z.
        self::assertSame('2024-01-15T08:00:00Z', $body['first_membership_date']);
        self::assertSame('2023-11-01T00:00:00Z', $body['registered_date']);

        self::assertArrayNotHasKey('civility', $body);
        self::assertArrayNotHasKey('age', $body);
        self::assertArrayNotHasKey('last_membership_date', $body);
        self::assertArrayNotHasKey('national', $body);
    }

    public function testCreateWithoutMembershipKeepsScalarsAndIncludesPresentAgeAndCivility(): void
    {
        $user = $this->createMock(Adherent::class);
        $user->method('getUuidAsString')->willReturn(self::USER_UUID);
        $user->method('getId')->willReturn(7);
        $user->tags = [];
        $user->method('getDeepZones')->willReturn([]);
        $user->agoraMemberships = new ArrayCollection();
        $user->expects(self::atLeastOnce())->method('findElectedRepresentativeMandates')->with(true)->willReturn([]);
        $user->method('getMandates')->willReturn(null);
        $user->method('getCommitteeMembership')->willReturn(null);
        $user->method('getGender')->willReturn('female');
        $user->method('getAge')->willReturn(28);
        $user->method('getFirstMembershipDonation')->willReturn(null);
        $user->method('getLastMembershipDonation')->willReturn(null);
        $user->method('getRegisteredAt')->willReturn(null);

        $body = $this->factory($user, [])->create($user)->jsonSerialize();

        self::assertSame([], $body['committees']);
        self::assertSame([], $body['agoras']);
        self::assertSame([], $body['mandate_types']);
        self::assertSame([], $body['declared_mandates']);
        self::assertSame(0, $body['committee_member']);
        self::assertSame('female', $body['civility']);
        self::assertSame(28, $body['age']);
        self::assertArrayNotHasKey('first_membership_date', $body);
        self::assertArrayNotHasKey('last_membership_date', $body);
        self::assertArrayNotHasKey('registered_date', $body);
    }

    private function factory(Adherent $user, array $scopeTargets): UserProfileFactory
    {
        $resolver = $this->createMock(UserScopeTargetResolver::class);
        $resolver->expects(self::atLeastOnce())->method('resolve')->with($user)->willReturn($scopeTargets);

        return new UserProfileFactory($resolver);
    }

    private function zone(string $type, string $code): Zone
    {
        $zone = $this->createStub(Zone::class);
        $zone->method('getType')->willReturn($type);
        $zone->method('getCode')->willReturn($code);

        return $zone;
    }

    private function committeeMembership(string $committeeUuid): CommitteeMembership
    {
        $committee = $this->createStub(Committee::class);
        $committee->method('getUuid')->willReturn(Uuid::fromString($committeeUuid));

        $membership = $this->createStub(CommitteeMembership::class);
        $membership->method('getCommittee')->willReturn($committee);

        return $membership;
    }

    private function agoraMembership(string $agoraUuid): AgoraMembership
    {
        $agora = $this->createStub(Agora::class);
        $agora->method('getUuid')->willReturn(Uuid::fromString($agoraUuid));

        $membership = $this->createStub(AgoraMembership::class);
        $membership->agora = $agora;

        return $membership;
    }

    private function mandate(string $type): ElectedRepresentativeAdherentMandate
    {
        $mandate = $this->createStub(ElectedRepresentativeAdherentMandate::class);
        $mandate->mandateType = $type;

        return $mandate;
    }
}
