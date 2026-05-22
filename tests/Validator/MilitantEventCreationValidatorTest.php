<?php

declare(strict_types=1);

namespace Tests\App\Validator;

use App\Entity\Adherent;
use App\Entity\Event\Event;
use App\Event\EventVisibilityEnum;
use App\Scope\GeneralScopeGenerator;
use App\Scope\ScopeEnum;
use App\Validator\MilitantEventCreation;
use App\Validator\MilitantEventCreationValidator;
use PHPUnit\Framework\MockObject\Stub;
use Symfony\Bundle\SecurityBundle\Security;

class MilitantEventCreationValidatorTest extends ConstraintValidatorTestCase
{
    private Security&Stub $security;
    private GeneralScopeGenerator&Stub $generalScopeGenerator;

    public function testSkipsWhenEventIsNotMilitant(): void
    {
        $this->validator->validate($this->event(authorScope: 'deputy'), new MilitantEventCreation());

        $this->assertNoViolation();
    }

    public function testRejectsNonPureMilitant(): void
    {
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
        $this->security->method('getUser')->willReturn($this->createStub(Adherent::class));
        $this->generalScopeGenerator->method('isPureMilitant')->willReturn(true);

        $this->validator->validate($this->event(), new MilitantEventCreation());

        $this->assertNoViolation();
    }

    protected function createValidator(): MilitantEventCreationValidator
    {
        $this->security = $this->createStub(Security::class);
        $this->generalScopeGenerator = $this->createStub(GeneralScopeGenerator::class);

        return new MilitantEventCreationValidator(
            $this->security,
            $this->generalScopeGenerator,
        );
    }

    private function event(
        EventVisibilityEnum $visibility = EventVisibilityEnum::PUBLIC,
        bool $hidden = false,
        ?string $authorScope = ScopeEnum::MILITANT,
    ): Event {
        $event = new Event();
        $event->visibility = $visibility;
        $event->hidden = $hidden;
        $event->setAuthorScope($authorScope);

        return $event;
    }
}
