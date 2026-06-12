<?php

declare(strict_types=1);

namespace Tests\App\History;

use App\Entity\Adherent;
use App\Entity\MyTeam\DelegatedAccess;
use App\History\Command\UserActionHistoryCommand;
use App\History\UserActionHistoryHandler;
use App\History\UserActionHistoryTypeEnum;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Uid\Uuid;

class UserActionHistoryHandlerTest extends TestCase
{
    private Security&Stub $security;
    private MessageBusInterface&MockObject $bus;
    private UserActionHistoryHandler $handler;

    protected function setUp(): void
    {
        $this->security = $this->createStub(Security::class);
        $this->bus = $this->createMock(MessageBusInterface::class);
        $this->handler = new UserActionHistoryHandler($this->security, $this->bus);
    }

    public function testCreateDelegatedAccessAddIncludesActorNameInData(): void
    {
        // Given
        $author = $this->createAdherent('Victorio', 'Fortest');
        $delegator = $this->createAdherent('Other', 'Person');
        $delegated = $this->createAdherent('Target', 'Adherent');

        $this->security->method('getUser')->willReturn($author);
        $this->security->method('getToken')->willReturn(null);

        $delegatedAccess = $this->createStub(DelegatedAccess::class);
        $delegatedAccess->method('getDelegator')->willReturn($delegator);
        $delegatedAccess->method('getDelegated')->willReturn($delegated);
        $delegatedAccess->method('getType')->willReturn('president_departmental_assembly');
        $delegatedAccess->method('getScopeFeatures')->willReturn(['messages']);
        $delegatedAccess->method('getRole')->willReturn('Responsable de communication');
        $delegatedAccess->roleCode = 'mobilization_manager';

        $this->bus
            ->expects(self::once())
            ->method('dispatch')
            ->with(self::callback(static function (UserActionHistoryCommand $command): bool {
                return UserActionHistoryTypeEnum::DELEGATED_ACCESS_ADD === $command->type
                    && 'Victorio Fortest' === ($command->data['actor_name'] ?? null);
            }))
            ->willReturn(new Envelope(new \stdClass()));

        // When
        $this->handler->createDelegatedAccessAdd($delegatedAccess);
    }

    public function testCreateDelegatedAccessAddActorNameIsNullWhenNoAuthor(): void
    {
        // Given
        $delegator = $this->createAdherent('Other', 'Person');
        $delegated = $this->createAdherent('Target', 'Adherent');

        $this->security->method('getUser')->willReturn(null);
        $this->security->method('getToken')->willReturn(null);

        $delegatedAccess = $this->createStub(DelegatedAccess::class);
        $delegatedAccess->method('getDelegator')->willReturn($delegator);
        $delegatedAccess->method('getDelegated')->willReturn($delegated);
        $delegatedAccess->method('getType')->willReturn('correspondent');
        $delegatedAccess->method('getScopeFeatures')->willReturn([]);
        $delegatedAccess->method('getRole')->willReturn('Correspondant');
        $delegatedAccess->roleCode = 'correspondent';

        $this->bus
            ->expects(self::once())
            ->method('dispatch')
            ->with(self::callback(static fn (UserActionHistoryCommand $command): bool => null === $command->data['actor_name']))
            ->willReturn(new Envelope(new \stdClass()));

        // When
        $this->handler->createDelegatedAccessAdd($delegatedAccess);
    }

    public function testCreateProfileUpdateConvertsKnownPropertiesToFrenchLabels(): void
    {
        // Given
        $adherent = $this->createAdherent('John', 'Doe');
        $this->security->method('getToken')->willReturn(null);

        $this->bus
            ->expects(self::once())
            ->method('dispatch')
            ->with(self::callback(static function (UserActionHistoryCommand $command): bool {
                return ['Date de naissance', 'Prénom'] === ($command->data['modified_field_labels'] ?? null);
            }))
            ->willReturn(new Envelope(new \stdClass()));

        // When
        $this->handler->createProfileUpdate($adherent, ['birthdate', 'first_name']);
    }

    public function testCreateProfileUpdateFallsBackToTechnicalNameForUnknownProperty(): void
    {
        // Given
        $adherent = $this->createAdherent('John', 'Doe');
        $this->security->method('getToken')->willReturn(null);

        $this->bus
            ->expects(self::once())
            ->method('dispatch')
            ->with(self::callback(static fn (UserActionHistoryCommand $command): bool => ['unknown_field'] === $command->data['modified_field_labels']))
            ->willReturn(new Envelope(new \stdClass()));

        // When
        $this->handler->createProfileUpdate($adherent, ['unknown_field']);
    }

    public function testCreateProfileUpdatePreservesOriginalProperties(): void
    {
        // Given
        $adherent = $this->createAdherent('John', 'Doe');
        $this->security->method('getToken')->willReturn(null);

        $this->bus
            ->expects(self::once())
            ->method('dispatch')
            ->with(self::callback(static fn (UserActionHistoryCommand $command): bool => ['first_name', 'birthdate'] === $command->data['properties']))
            ->willReturn(new Envelope(new \stdClass()));

        // When
        $this->handler->createProfileUpdate($adherent, ['first_name', 'birthdate']);
    }

    private function createAdherent(string $firstName, string $lastName): Adherent
    {
        $adherent = $this->createStub(Adherent::class);
        $adherent->method('getFirstName')->willReturn($firstName);
        $adherent->method('getLastName')->willReturn($lastName);
        $adherent->method('getUuid')->willReturn(Uuid::v4());
        $adherent->method('findZoneBasedRole')->willReturn(null);
        $adherent->method('equals')->willReturn(false);

        return $adherent;
    }
}
