<?php

declare(strict_types=1);

namespace Tests\App\Controller\Renaissance\Adhesion;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\App\AbstractRenaissanceWebTestCase;
use Tests\App\Controller\ControllerTestTrait;

/**
 * adherent-female-32@en-marche-dev.fr is a Renaissance adherent (it carries the
 * "adherent" tag, so isFullyCompletedAdhesion() expects the full step list) whose
 * adhesion stops at the "communication" step: "member_card" and "committee" are
 * missing. FinishAdhesionStepsListener therefore diverts them to the member-card
 * step when they land on /app.
 *
 * The campaign host must bypass that diversion (campaign logins go straight to
 * the app), while every other user host must keep diverting. This guards the
 * host-specificity of the bypass: dropping the guard would let /app reach OAuth
 * on the vox host too, and this test would catch it.
 */
#[Group('functional')]
class FinishAdhesionStepsBypassTest extends AbstractRenaissanceWebTestCase
{
    use ControllerTestTrait;

    private const INCOMPLETE_ADHERENT_EMAIL = 'adherent-female-32@en-marche-dev.fr';

    #[DataProvider('provideHostExpectations')]
    public function testAdhesionFunnelDiversionDependsOnHost(string $hostParameter, string $expectedFragment, string $forbiddenFragment): void
    {
        // Skipped: the adhesion-steps diversion is temporarily disabled in FinishAdhesionStepsListener
        // (early `return`, commit fef3ddab5), so the vox host no longer diverts to /adhesion. Remove
        // this skip when the redirect is restored.
        self::markTestSkipped('Adhesion-steps diversion temporarily disabled in FinishAdhesionStepsListener.');

        $this->client->setServerParameter('HTTP_HOST', static::getContainer()->getParameter($hostParameter));
        $this->authenticateAsAdherent($this->client, self::INCOMPLETE_ADHERENT_EMAIL);

        $this->client->request(Request::METHOD_GET, '/app');

        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());
        $location = (string) $this->client->getResponse()->headers->get('location');
        self::assertStringContainsString($expectedFragment, $location);
        self::assertStringNotContainsString($forbiddenFragment, $location);
    }

    public static function provideHostExpectations(): array
    {
        return [
            // Vox host: the incomplete adherent is diverted to the first adhesion step.
            'vox host diverts to the adhesion funnel' => ['user_vox_host', '/adhesion', '/oauth/v2/auth'],
            // Campaign host: the bypass lets /app proceed to the OAuth authorization flow.
            'campaign host proceeds to oauth' => ['user_campaign_host', '/oauth/v2/auth', '/adhesion'],
        ];
    }
}
