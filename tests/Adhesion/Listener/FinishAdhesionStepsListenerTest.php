<?php

declare(strict_types=1);

namespace Tests\App\Adhesion\Listener;

use App\Adhesion\AdhesionStepEnum;
use App\Adhesion\Listener\FinishAdhesionStepsListener;
use App\AppCodeEnum;
use App\Entity\Adherent;
use App\OAuth\App\AuthAppUrlManager;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class FinishAdhesionStepsListenerTest extends TestCase
{
    public function testSignupAccountIsNotRedirected(): void
    {
        $adherent = $this->createStub(Adherent::class);
        $adherent->signupAccount = true;
        // Even with an incomplete adhesion, a signup account must never be auto-redirected.
        $adherent->method('isFullyCompletedAdhesion')->willReturn(false);

        $security = $this->createStub(Security::class);
        $security->method('getUser')->willReturn($adherent);

        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $urlGenerator->expects(self::never())->method('generate');

        $event = $this->createMainRequestEvent('vox_app_redirect');
        new FinishAdhesionStepsListener($security, $urlGenerator, $this->createAppUrlManager(null))->onRequestEvent($event);

        self::assertNull($event->getResponse());
    }

    public function testNonSignupAccountWithIncompleteAdhesionIsRedirected(): void
    {
        $adherent = $this->createStub(Adherent::class);
        $adherent->signupAccount = false;
        $adherent->method('isFullyCompletedAdhesion')->willReturn(false);
        $adherent->method('isRenaissanceAdherent')->willReturn(false);
        $adherent->method('getFinishedAdhesionSteps')->willReturn([]);

        $security = $this->createStub(Security::class);
        $security->method('getUser')->willReturn($adherent);
        $security->method('isGranted')->willReturn(false);

        $expectedRoute = AdhesionStepEnum::getNextStep(false, []);
        self::assertNotNull($expectedRoute, 'precondition: an incomplete sympathisant has a next step');

        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $urlGenerator
            ->expects(self::once())
            ->method('generate')
            ->with($expectedRoute)
            ->willReturn('/adhesion/informations')
        ;

        $event = $this->createMainRequestEvent('vox_app_redirect');
        new FinishAdhesionStepsListener($security, $urlGenerator, $this->createAppUrlManager(null))->onRequestEvent($event);

        self::assertInstanceOf(RedirectResponse::class, $event->getResponse());
    }

    public function testImpersonatorIsNotRedirected(): void
    {
        $adherent = $this->createStub(Adherent::class);
        $adherent->signupAccount = false;
        $adherent->method('isFullyCompletedAdhesion')->willReturn(false);

        $security = $this->createStub(Security::class);
        $security->method('getUser')->willReturn($adherent);
        $security->method('isGranted')->willReturn(true);

        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $urlGenerator->expects(self::never())->method('generate');

        $event = $this->createMainRequestEvent('vox_app_redirect');
        new FinishAdhesionStepsListener($security, $urlGenerator, $this->createAppUrlManager(null))->onRequestEvent($event);

        self::assertNull($event->getResponse());
    }

    public function testCampaignHostIsNotRedirected(): void
    {
        // An incomplete adhesion that would normally be redirected...
        $adherent = $this->createStub(Adherent::class);
        $adherent->signupAccount = false;
        $adherent->method('isFullyCompletedAdhesion')->willReturn(false);

        $security = $this->createStub(Security::class);
        $security->method('getUser')->willReturn($adherent);
        $security->method('isGranted')->willReturn(false);

        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $urlGenerator->expects(self::never())->method('generate');

        $event = $this->createMainRequestEvent('vox_app_redirect');
        // ...is left untouched when the request belongs to the campaign app.
        new FinishAdhesionStepsListener($security, $urlGenerator, $this->createAppUrlManager(AppCodeEnum::CAMPAIGN))->onRequestEvent($event);

        self::assertNull($event->getResponse());
    }

    private function createMainRequestEvent(string $route): RequestEvent
    {
        $request = new Request();
        $request->attributes->set('_route', $route);

        return new RequestEvent(
            $this->createStub(HttpKernelInterface::class),
            $request,
            HttpKernelInterface::MAIN_REQUEST
        );
    }

    private function createAppUrlManager(?string $appCode): AuthAppUrlManager
    {
        $appUrlManager = $this->createStub(AuthAppUrlManager::class);
        $appUrlManager->method('getAppCodeFromRequest')->willReturn($appCode);

        return $appUrlManager;
    }
}
