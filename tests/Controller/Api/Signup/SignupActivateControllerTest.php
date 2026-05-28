<?php

declare(strict_types=1);

namespace Tests\App\Controller\Api\Signup;

use App\Adhesion\ActivationCodeManager;
use App\Entity\Adherent;
use App\Entity\AdherentActivationCode;
use App\Entity\PostAddress;
use App\Membership\ActivityPositionsEnum;
use App\Membership\Signup\SignupCode;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\App\AbstractApiTestCase;
use Tests\App\Controller\ControllerTestTrait;

#[Group('functional')]
#[Group('api')]
class SignupActivateControllerTest extends AbstractApiTestCase
{
    use ControllerTestTrait;

    private const URL = '/api/signup/activate';
    private const CLIENT_IP = '127.0.0.1';

    public function testActivateValidCodeEnablesAdherent(): void
    {
        $adherent = $this->createPendingAdherent('activate-success@example.test');
        $code = $this->generateCode($adherent);

        $this->post(['email' => $adherent->getEmailAddress(), 'code' => $code->value]);

        $this->assertResponseStatusCode(Response::HTTP_NO_CONTENT, $this->client->getResponse());

        $this->manager->clear();
        $reloaded = $this->getAdherentRepository()->findOneByEmail($adherent->getEmailAddress());
        self::assertTrue($reloaded->isEnabled(), 'PENDING → ENABLED transition must happen on a valid code.');
    }

    public function testActivateWrongCodeReturnsUniformError(): void
    {
        $adherent = $this->createPendingAdherent('activate-wrong-code@example.test');
        $this->generateCode($adherent);

        $this->post(['email' => $adherent->getEmailAddress(), 'code' => '000']);

        $this->assertResponseStatusCode(Response::HTTP_BAD_REQUEST, $this->client->getResponse());
        $body = json_decode((string) $this->client->getResponse()->getContent(), true);
        self::assertSame(['error' => 'invalid_or_expired'], $body);

        // No transition for an invalid code.
        $this->manager->clear();
        self::assertTrue($this->getAdherentRepository()->findOneByEmail($adherent->getEmailAddress())->isPending());
    }

    public function testActivateUnknownEmailReturnsUniformError(): void
    {
        $this->post(['email' => 'never-signed-up@example.test', 'code' => '123']);

        $this->assertResponseStatusCode(Response::HTTP_BAD_REQUEST, $this->client->getResponse());
        $body = json_decode((string) $this->client->getResponse()->getContent(), true);
        self::assertSame(['error' => 'invalid_or_expired'], $body);
    }

    public function testActivateEnabledAdherentReturnsUniformError(): void
    {
        // An already ENABLED adherent must not be re-activated via the public endpoint.
        $email = 'activate-already-enabled@example.test';
        $adherent = $this->createPendingAdherent($email);
        $adherent->enable();
        $this->manager->flush();
        $this->generateCode($adherent);

        $this->post(['email' => $email, 'code' => '123']);

        $this->assertResponseStatusCode(Response::HTTP_BAD_REQUEST, $this->client->getResponse());
        $body = json_decode((string) $this->client->getResponse()->getContent(), true);
        self::assertSame(['error' => 'invalid_or_expired'], $body);
    }

    public function testActivateInvalidCodeFormatReturnsRfc7807Violation(): void
    {
        $adherent = $this->createPendingAdherent('activate-invalid-format@example.test');
        $this->generateCode($adherent);

        // 4 digits instead of 3 — the regex must reject it before any DB lookup.
        // MapRequestPayload validation failures default to 422 (Symfony convention).
        $this->post(['email' => $adherent->getEmailAddress(), 'code' => '1234']);

        $this->assertResponseStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY, $this->client->getResponse());
        SignupApiErrorAssertions::assertValidationErrorShape($this->client->getResponse());
    }

    public function testActivateMalformedJsonReturnsRfc7807BadRequest(): void
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

    public function testActivateRateLimitedByIp(): void
    {
        // signup_code_attempt = 10/min : the 11th request from the same IP must be 429.
        for ($i = 1; $i <= 10; ++$i) {
            $this->post(['email' => 'rate-limit-loop@example.test', 'code' => '000']);
            $status = $this->client->getResponse()->getStatusCode();
            self::assertNotSame(Response::HTTP_TOO_MANY_REQUESTS, $status, "Iteration {$i} must not be rate-limited yet.");
        }

        $this->post(['email' => 'rate-limit-loop@example.test', 'code' => '000']);
        $this->assertResponseStatusCode(Response::HTTP_TOO_MANY_REQUESTS, $this->client->getResponse());
    }

    public function testActivateExhaustedPerAccountRetryBudgetRejectsEvenValidCode(): void
    {
        // activation_account_retry = 3/min, keyed by adherent UUID and consumed inside checkCode()
        // BEFORE the code lookup. createPendingAdherent() resets it, so this adherent starts with a
        // full quota of 3. The IP limiter (10/min) stays well under, so it cannot explain a rejection.
        $adherent = $this->createPendingAdherent('activate-account-limiter@example.test');
        $code = $this->generateCode($adherent);

        // Three wrong attempts exhaust the per-account budget (failedAttempts stays < 5, so the code
        // itself is NOT revoked — only the account retry budget is spent).
        for ($i = 1; $i <= 3; ++$i) {
            $this->post(['email' => $adherent->getEmailAddress(), 'code' => '000']);
            $this->assertResponseStatusCode(Response::HTTP_BAD_REQUEST, $this->client->getResponse());
        }

        // The 4th attempt carries the VALID code: only the per-account limiter can reject it here
        // (the IP limiter allows 10/min). A 400 + still-PENDING adherent proves the limiter is wired.
        $this->post(['email' => $adherent->getEmailAddress(), 'code' => $code->value]);

        $this->assertResponseStatusCode(Response::HTTP_BAD_REQUEST, $this->client->getResponse());
        $body = json_decode((string) $this->client->getResponse()->getContent(), true);
        self::assertSame(['error' => 'invalid_or_expired'], $body);

        $this->manager->clear();
        self::assertTrue(
            $this->getAdherentRepository()->findOneByEmail($adherent->getEmailAddress())->isPending(),
            'A valid code presented after the per-account retry budget is exhausted must NOT enable the account.'
        );
    }

    protected function setUp(): void
    {
        parent::setUp();

        // The signup_code_attempt limiter persists across kernel boots within the same test process.
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

        // Reset the per-adherent retry limiter so a fresh adherent starts with a full quota.
        self::getContainer()
            ->get('limiter.activation_account_retry')
            ->create('activation_code.validate.'.$adherent->getUuidAsString())
            ->reset()
        ;

        return $adherent;
    }

    private function generateCode(Adherent $adherent): AdherentActivationCode
    {
        return self::getContainer()->get(ActivationCodeManager::class)->generate(
            $adherent,
            force: true,
            codeLength: SignupCode::LENGTH,
        );
    }
}
