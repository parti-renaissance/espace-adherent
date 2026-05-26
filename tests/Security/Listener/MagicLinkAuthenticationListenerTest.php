<?php

declare(strict_types=1);

namespace Tests\App\Security\Listener;

use App\Adhesion\AdhesionStepEnum;
use App\Controller\Renaissance\MagicLinkController;
use App\Entity\Adherent;
use App\Entity\PostAddress;
use App\Membership\ActivityPositionsEnum;
use App\Membership\Event\UserEvent;
use App\Membership\UserEvents;
use App\Security\Listener\MagicLinkAuthenticationListener;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Event\AuthenticationSuccessEvent;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class MagicLinkAuthenticationListenerTest extends TestCase
{
    private EntityManagerInterface $entityManager;
    private RequestStack $requestStack;
    private EventDispatcherInterface $dispatcher;
    private MagicLinkAuthenticationListener $listener;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->requestStack = new RequestStack();
        $this->dispatcher = $this->createMock(EventDispatcherInterface::class);

        $this->listener = new MagicLinkAuthenticationListener(
            $this->entityManager,
            $this->requestStack,
            $this->dispatcher,
        );
    }

    public function testOnAuthenticationSuccessDispatchesUserValidatedWhenPending(): void
    {
        $this->pushRequestOnMagicLinkRoute();
        $adherent = $this->createPendingAdherent();

        $this->entityManager->expects(self::once())->method('flush');

        $this->dispatcher
            ->expects(self::once())
            ->method('dispatch')
            ->with(
                self::callback(static fn (UserEvent $event): bool => $event->getAdherent() === $adherent),
                UserEvents::USER_VALIDATED,
            )
        ;

        $this->listener->onAuthenticationSuccess($this->buildEvent($adherent));

        self::assertTrue($adherent->isEnabled());
        self::assertTrue($adherent->hasFinishedAdhesionStep(AdhesionStepEnum::ACTIVATION));
    }

    public function testOnAuthenticationSuccessDoesNotDispatchWhenAlreadyEnabled(): void
    {
        $this->pushRequestOnMagicLinkRoute();
        $adherent = $this->createPendingAdherent();
        $adherent->enable();

        $this->entityManager->expects(self::once())->method('flush');
        $this->dispatcher->expects(self::never())->method('dispatch');

        $this->listener->onAuthenticationSuccess($this->buildEvent($adherent));

        self::assertTrue($adherent->isEnabled());
    }

    public function testOnAuthenticationSuccessDoesNothingOnDifferentRoute(): void
    {
        $request = new Request();
        $request->attributes->set('_route', 'some_other_route');
        $this->requestStack->push($request);

        $this->entityManager->expects(self::never())->method('flush');
        $this->dispatcher->expects(self::never())->method('dispatch');

        $adherent = $this->createPendingAdherent();
        $this->listener->onAuthenticationSuccess($this->buildEvent($adherent));

        self::assertTrue($adherent->isPending());
    }

    public function testOnAuthenticationSuccessIgnoresNonAdherentUser(): void
    {
        $this->pushRequestOnMagicLinkRoute();

        $this->entityManager->expects(self::never())->method('flush');
        $this->dispatcher->expects(self::never())->method('dispatch');

        $token = $this->createStub(TokenInterface::class);
        $token->method('getUser')->willReturn(null);

        $this->listener->onAuthenticationSuccess(new AuthenticationSuccessEvent($token));
    }

    private function pushRequestOnMagicLinkRoute(): void
    {
        $request = new Request();
        $request->attributes->set('_route', MagicLinkController::ROUTE_NAME);
        $this->requestStack->push($request);
    }

    private function buildEvent(Adherent $adherent): AuthenticationSuccessEvent
    {
        $token = $this->createStub(TokenInterface::class);
        $token->method('getUser')->willReturn($adherent);

        return new AuthenticationSuccessEvent($token);
    }

    private function createPendingAdherent(): Adherent
    {
        return Adherent::create(
            Adherent::createUuid('jane.doe@example.org'),
            'ABC-001',
            'jane.doe@example.org',
            null,
            'female',
            'Jane',
            'Doe',
            new \DateTime('1990-01-01'),
            ActivityPositionsEnum::EMPLOYED,
            PostAddress::createFrenchAddress('1 rue de Paris', '75001-75101'),
        );
    }
}
