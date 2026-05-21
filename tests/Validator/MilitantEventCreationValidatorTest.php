<?php

declare(strict_types=1);

namespace Tests\App\Validator;

use App\Entity\Adherent;
use App\Entity\Event\Event;
use App\Event\EventVisibilityEnum;
use App\Scope\GeneralScopeGenerator;
use App\Scope\Scope;
use App\Scope\ScopeGeneratorResolver;
use App\Validator\MilitantEventCreation;
use App\Validator\MilitantEventCreationValidator;
use PHPUnit\Framework\MockObject\Stub;
use Symfony\Bundle\SecurityBundle\Security;

class MilitantEventCreationValidatorTest extends ConstraintValidatorTestCase
{
    private Security&Stub $security;
    private ScopeGeneratorResolver&Stub $scopeGeneratorResolver;
    private GeneralScopeGenerator&Stub $generalScopeGenerator;

    public function testSkipsWhenScopeNotMilitant(): void
    {
        $this->scopeGeneratorResolver->method('generate')->willReturn($this->scope('deputy'));

        $this->validator->validate($this->event(), new MilitantEventCreation());

        $this->assertNoViolation();
    }

    public function testRejectsNonPureMilitant(): void
    {
        $this->scopeGeneratorResolver->method('generate')->willReturn($this->scope('militant'));
        $this->security->method('getUser')->willReturn($this->createStub(Adherent::class));
        $this->generalScopeGenerator->method('isPureMilitant')->willReturn(false);

        $this->validator->validate($this->event(), new MilitantEventCreation());

        $this
            ->buildViolation(new MilitantEventCreation()->notPureMilitantMessage)
            ->assertRaised()
        ;
    }

    public function testRejectsNonPublicVisibility(): void
    {
        $this->scopeGeneratorResolver->method('generate')->willReturn($this->scope('militant'));
        $this->security->method('getUser')->willReturn($this->createStub(Adherent::class));
        $this->generalScopeGenerator->method('isPureMilitant')->willReturn(true);

        $this->validator->validate($this->event(EventVisibilityEnum::ADHERENT), new MilitantEventCreation());

        $this
            ->buildViolation(new MilitantEventCreation()->visibilityMessage)
            ->atPath('property.path.visibility')
            ->assertRaised()
        ;
    }

    public function testRejectsHiddenEvent(): void
    {
        $this->scopeGeneratorResolver->method('generate')->willReturn($this->scope('militant'));
        $this->security->method('getUser')->willReturn($this->createStub(Adherent::class));
        $this->generalScopeGenerator->method('isPureMilitant')->willReturn(true);

        $this->validator->validate($this->event(EventVisibilityEnum::PUBLIC, true), new MilitantEventCreation());

        $this
            ->buildViolation(new MilitantEventCreation()->visibilityMessage)
            ->atPath('property.path.visibility')
            ->assertRaised()
        ;
    }

    public function testAcceptsPureMilitantPublicEvent(): void
    {
        $this->scopeGeneratorResolver->method('generate')->willReturn($this->scope('militant'));
        $this->security->method('getUser')->willReturn($this->createStub(Adherent::class));
        $this->generalScopeGenerator->method('isPureMilitant')->willReturn(true);

        $this->validator->validate($this->event(), new MilitantEventCreation());

        $this->assertNoViolation();
    }

    protected function createValidator(): MilitantEventCreationValidator
    {
        $this->security = $this->createStub(Security::class);
        $this->scopeGeneratorResolver = $this->createStub(ScopeGeneratorResolver::class);
        $this->generalScopeGenerator = $this->createStub(GeneralScopeGenerator::class);

        return new MilitantEventCreationValidator(
            $this->security,
            $this->scopeGeneratorResolver,
            $this->generalScopeGenerator,
        );
    }

    private function scope(string $code): Scope
    {
        return new Scope($code, $code, $code, [], [], [], $this->createStub(Adherent::class));
    }

    private function event(EventVisibilityEnum $visibility = EventVisibilityEnum::PUBLIC, bool $hidden = false): Event
    {
        $event = new Event();
        $event->visibility = $visibility;
        $event->hidden = $hidden;

        return $event;
    }
}
