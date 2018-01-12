<?php

namespace Tests\AppBundle\User;

use AppBundle\Entity\OAuth\Client;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\OAuth\UserAuthorization;
use AppBundle\OAuth\Model\Scope;
use AppBundle\Repository\OAuth\UserAuthorizationRepository;
use AppBundle\OAuth\OAuthAuthorizationManager;
use PHPUnit\Framework\TestCase;

class OAuthAuthorizationManagerTest extends TestCase
{
    /** @var UserAuthorizationRepository|\PHPUnit_Framework_MockObject_MockObject */
    private $userAuthorizationRepository;

    public function testIsAuthorizeReturnFalseWhenClientScopeIsSupported(): void
    {
        $userAuthorizationManager = $this->createOAuthAuthorizationManager();

        $this->userAuthorizationRepository->expects($this->never())->method($this->anything());

        static::assertFalse(
            $userAuthorizationManager->isAuthorized($this->createUser(), $this->createClient(), [new Scope('Not Allowed')])
        );
        static::assertFalse( // Test with Client allowed not to ask for user authorization
            $userAuthorizationManager->isAuthorized($this->createUser(), $this->createClient(false), [new Scope('Not Allowed')])
        );
    }

    public function testIsAuthorizedReturnTrueWhenClientScopesAndUserAuthorizationScopesAreValid(): void
    {
        $userAuthorizationManager = $this->createOAuthAuthorizationManager();
        $scopes = [new Scope('public')];
        $user = $this->createUser();
        $client = $this->createClient();

        $this->userAuthorizationRepository
            ->expects($this->once())
            ->method('findByUserAndClient')
            ->with($user, $client)
            ->willReturn($this->createUserAuthorization($user, $client));

        static::assertTrue(
            $userAuthorizationManager->isAuthorized($user, $client, $scopes)
        );
    }

    public function testIsAuthorizedReturnTrueForClientAllowedToByPassUserAuthorization(): void
    {
        $userAuthorizationManager = $this->createOAuthAuthorizationManager();

        $this->userAuthorizationRepository->expects($this->never())->method($this->anything());

        static::assertTrue(
            $userAuthorizationManager->isAuthorized($this->createUser(), $this->createClient(false), [new Scope('public')])
        );
    }

    public function testIsAuthorizedReturnFalseIfScopeIsNotAllowedByTheUser(): void
    {
        $userAuthorizationManager = $this->createOAuthAuthorizationManager();
        $user = $this->createUser();
        $client = $this->createClient();

        $this->userAuthorizationRepository
            ->expects($this->once())
            ->method('findByUserAndClient')
            ->with($user, $client)
            ->willReturn($this->createUserAuthorization($user, $client, [new Scope('scope1')]));

        static::assertFalse(
            $userAuthorizationManager->isAuthorized($user, $client, [new Scope('public')])
        );
    }

    public function testRecordUpdateUserAuthorization(): void
    {
        $userAuthorizationManager = $this->createOAuthAuthorizationManager();
        $user = $this->createUser();
        $client = $this->createClient();

        $this->userAuthorizationRepository
            ->expects($this->once())
            ->method('findByUserAndClient')
            ->with($user, $client)
            ->willReturn($userAuthorization = $this->createUserAuthorization($user, $client, [new Scope('public')]));

        $this->userAuthorizationRepository
            ->expects($this->once())
            ->method('save')
            ->with($userAuthorization);

        $scopes = [new Scope('scope1'), new Scope('scope2')];
        $userAuthorizationManager->record($user, $client, $scopes);
        static::assertTrue($userAuthorization->supportsScopes($scopes));
    }

    public function testRecordCreateUserAuthorization(): void
    {
        $userAuthorizationManager = $this->createOAuthAuthorizationManager();
        $user = $this->createUser();
        $client = $this->createClient();

        $this->userAuthorizationRepository
            ->expects($this->once())
            ->method('findByUserAndClient')
            ->with($user, $client)
            ->willReturn(null);

        $this->userAuthorizationRepository
            ->expects($this->once())
            ->method('save');

        $userAuthorizationManager->record($user, $client, [new Scope('scope2')]);
    }

    public function testRecordDoesNotPersistUserAuthorizationWhenClientIsAllowedNotToAskUserAuthorization(): void
    {
        $userAuthorizationManager = $this->createOAuthAuthorizationManager();

        $this->userAuthorizationRepository
            ->expects($this->never())
            ->method('findByUserAndClient');

        $this->userAuthorizationRepository
            ->expects($this->never())
            ->method('save');

        $userAuthorizationManager->record($this->createUser(), $this->createClient(false), [new Scope('scope2')]);
    }

    private function createUser(): Adherent
    {
        return $this->createMock(Adherent::class);
    }

    private function createClient(bool $askForUserAuthorization = true): Client
    {
        $client = new Client();
        $client->addSupportedScope('public');
        $client->setAskUserForAuthorization($askForUserAuthorization);

        return $client;
    }

    private function createOAuthAuthorizationManager(): OAuthAuthorizationManager
    {
        $this->userAuthorizationRepository = $this->createMock(UserAuthorizationRepository::class);

        return new OAuthAuthorizationManager($this->userAuthorizationRepository);
    }

    private function createUserAuthorization(Adherent $user, Client $client, array $scopes = [])
    {
        return new UserAuthorization(null, $user, $client, $scopes ?: [new Scope('public')]);
    }
}
