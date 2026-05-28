<?php

declare(strict_types=1);

namespace Tests\App\Controller\Api\Signup;

use App\Adhesion\ActivationCodeManager;
use App\Entity\Adherent;
use App\Entity\PostAddress;
use App\Mailer\Message\Renaissance\SignupConfirmationMessage;
use App\Membership\ActivityPositionsEnum;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\App\AbstractApiTestCase;
use Tests\App\Controller\ControllerTestTrait;

#[Group('functional')]
#[Group('api')]
class SignupResendCodeControllerTest extends AbstractApiTestCase
{
    use ControllerTestTrait;

    private const URL = '/api/signup/resend-code';
    private const CLIENT_IP = '127.0.0.1';

    public function testResendForPendingAdherentSendsConfirmationMail(): void
    {
        $email = 'resend-pending@example.test';
        $this->createPendingAdherent($email);

        $this->post(['email' => $email]);

        $this->assertResponseStatusCode(Response::HTTP_NO_CONTENT, $this->client->getResponse());
        $this->assertCountMails(1, SignupConfirmationMessage::class, $email);
    }

    public function testResendForUnknownEmailReturnsUniform204AndSendsNoMail(): void
    {
        // Anti-enumeration: an unknown email must not leak its absence via status code.
        $this->post(['email' => 'never-existed@example.test']);

        $this->assertResponseStatusCode(Response::HTTP_NO_CONTENT, $this->client->getResponse());
        $this->assertCountMails(0, SignupConfirmationMessage::class, 'never-existed@example.test');
    }

    public function testResendForEnabledAdherentReturns204AndSendsNoMail(): void
    {
        $email = 'resend-enabled@example.test';
        $adherent = $this->createPendingAdherent($email);
        $adherent->enable();
        $this->manager->flush();

        $this->post(['email' => $email]);

        $this->assertResponseStatusCode(Response::HTTP_NO_CONTENT, $this->client->getResponse());
        $this->assertCountMails(0, SignupConfirmationMessage::class, $email);
    }

    public function testResendRejectsMalformedEmailWithRfc7807Violation(): void
    {
        $this->post(['email' => 'not-an-email']);

        $this->assertResponseStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY, $this->client->getResponse());
        SignupApiErrorAssertions::assertValidationErrorShape($this->client->getResponse());
    }

    public function testResendRejectsMalformedJsonWithRfc7807BadRequest(): void
    {
        $this->client->request(
            Request::METHOD_POST,
            self::URL,
            [],
            [],
            ['CONTENT_TYPE' => 'application/json', 'REMOTE_ADDR' => self::CLIENT_IP],
            '{"email": "broken", '
        );

        $this->assertResponseStatusCode(Response::HTTP_BAD_REQUEST, $this->client->getResponse());
        SignupApiErrorAssertions::assertBadRequestErrorShape($this->client->getResponse());
    }

    public function testResendBackoffSilentlySkipsMailOnImmediateSecondCall(): void
    {
        // Exponential backoff: a code generated less than 30 s ago must skip the dispatch silently.
        // The response stays a uniform 204 — a 429 would leak account state by contrasting with the
        // 204 returned for unknown/non-pending emails (anti-enumeration leak).
        $email = 'resend-backoff@example.test';
        $adherent = $this->createPendingAdherent($email);

        // Simulate a fresh code created moments before the resend attempt.
        self::getContainer()->get(ActivationCodeManager::class)->generate(
            $adherent,
            force: true,
            codeLength: 3,
        );

        $this->post(['email' => $email]);

        $this->assertResponseStatusCode(Response::HTTP_NO_CONTENT, $this->client->getResponse());
        // Side-effect-only observability: no mail dispatched because the backoff window is active.
        $this->assertCountMails(0, SignupConfirmationMessage::class, $email);
    }

    public function testResendRateLimitedByIp(): void
    {
        // signup_code_attempt = 10/min : the 11th request from the same IP must be 429.
        for ($i = 1; $i <= 10; ++$i) {
            $this->post(['email' => "resend-ip-loop-{$i}@example.test"]);
            self::assertNotSame(
                Response::HTTP_TOO_MANY_REQUESTS,
                $this->client->getResponse()->getStatusCode(),
                "Iteration {$i} must not be IP rate-limited yet."
            );
        }

        $this->post(['email' => 'resend-ip-loop-overflow@example.test']);
        $this->assertResponseStatusCode(Response::HTTP_TOO_MANY_REQUESTS, $this->client->getResponse());
    }

    protected function setUp(): void
    {
        parent::setUp();

        self::getContainer()->get('limiter.signup_code_attempt')->create(self::CLIENT_IP)->reset();
    }

    private function post(array $payload): void
    {
        $this->client->request(
            Request::METHOD_POST,
            self::URL,
            [],
            [],
            ['CONTENT_TYPE' => 'application/json', 'REMOTE_ADDR' => self::CLIENT_IP],
            json_encode($payload)
        );
    }

    private function createPendingAdherent(string $email): Adherent
    {
        $adherent = Adherent::create(
            Adherent::createUuid($email),
            substr(bin2hex(random_bytes(4)), 0, 7),
            $email,
            null,
            'female',
            'Jane',
            'Doe',
            new \DateTime('1990-01-01'),
            ActivityPositionsEnum::EMPLOYED,
            PostAddress::createFrenchAddress('1 rue de Paris', '75001-75101'),
        );
        $adherent->setStatus(Adherent::PENDING);

        $this->manager->persist($adherent);
        $this->manager->flush();

        return $adherent;
    }
}
