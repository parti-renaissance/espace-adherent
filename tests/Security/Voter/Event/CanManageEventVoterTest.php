<?php

declare(strict_types=1);

namespace Tests\App\Security\Voter\Event;

use App\Entity\Adherent;
use App\Entity\Event\Event;
use App\Repository\Geo\ZoneRepository;
use App\Scope\FeatureEnum;
use App\Scope\GeneralScopeGenerator;
use App\Scope\Scope;
use App\Scope\ScopeEnum;
use App\Scope\ScopeGeneratorResolver;
use App\Security\Voter\Event\CanManageEventVoter;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class CanManageEventVoterTest extends TestCase
{
    public function testMilitantCanManageOwnEvent(): void
    {
        $adherent = $this->createStub(Adherent::class);

        $event = $this->createStub(Event::class);
        $event->method('getAuthor')->willReturn($adherent);
        $event->method('getAuthorScope')->willReturn(ScopeEnum::MILITANT);

        self::assertSame(
            VoterInterface::ACCESS_GRANTED,
            $this->voteOnEvent($adherent, $event, $this->militantScope($adherent)),
        );
    }

    public function testMilitantCannotManageOthersEvent(): void
    {
        $adherent = $this->createStub(Adherent::class);

        $event = $this->createStub(Event::class);
        $event->method('getAuthor')->willReturn($this->createStub(Adherent::class));
        $event->method('getAuthorScope')->willReturn(ScopeEnum::MILITANT);

        self::assertSame(
            VoterInterface::ACCESS_DENIED,
            $this->voteOnEvent($adherent, $event, $this->militantScope($adherent)),
        );
    }

    public function testMilitantCanManageOwnEventItemViaDirectCheck(): void
    {
        $adherent = $this->createStub(Adherent::class);
        $adherent->method('getUuidAsString')->willReturn('uuid-1');

        $generalScopeGenerator = $this->createStub(GeneralScopeGenerator::class);
        $generalScopeGenerator->method('generateScopes')->willReturn([$this->militantScope($adherent)]);

        $voter = new CanManageEventVoter(
            $this->createStub(ScopeGeneratorResolver::class),
            $generalScopeGenerator,
            $this->createStub(ZoneRepository::class),
        );

        $token = $this->createStub(TokenInterface::class);
        $token->method('getUser')->willReturn($adherent);

        self::assertSame(
            VoterInterface::ACCESS_GRANTED,
            $voter->vote($token, ['author_uuid' => 'uuid-1', 'author_scope' => ScopeEnum::MILITANT], [CanManageEventVoter::CAN_MANAGE_EVENT_ITEM]),
        );
    }

    private function militantScope(Adherent $adherent): Scope
    {
        return new Scope('militant', 'Militant', 'Militant', [], [], [FeatureEnum::EVENTS], $adherent);
    }

    private function voteOnEvent(Adherent $adherent, Event $event, Scope $scope): int
    {
        $resolver = $this->createStub(ScopeGeneratorResolver::class);
        $resolver->method('generate')->willReturn($scope);

        $voter = new CanManageEventVoter(
            $resolver,
            $this->createStub(GeneralScopeGenerator::class),
            $this->createStub(ZoneRepository::class),
        );

        $token = $this->createStub(TokenInterface::class);
        $token->method('getUser')->willReturn($adherent);

        return $voter->vote($token, $event, [CanManageEventVoter::CAN_MANAGE_EVENT]);
    }
}
