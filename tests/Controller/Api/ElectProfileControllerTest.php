<?php

declare(strict_types=1);

namespace Tests\App\Controller\Api;

use App\Adherent\Contribution\ContributionStatusEnum;
use App\DataFixtures\ORM\LoadAdherentData;
use App\DataFixtures\ORM\LoadClientData;
use App\DataFixtures\ORM\LoadContributionData;
use App\Entity\Adherent;
use App\Entity\Contribution\Contribution;
use App\OAuth\Model\GrantTypeEnum;
use App\OAuth\Model\Scope;
use App\Repository\AdherentRepository;
use App\Repository\Contribution\ContributionRepository;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\App\AbstractApiTestCase;
use Tests\App\Controller\ApiControllerTestTrait;
use Tests\App\Controller\ControllerTestTrait;
use Tests\App\GoCardless\DummyClient;

#[Group('functional')]
#[Group('api')]
class ElectProfileControllerTest extends AbstractApiTestCase
{
    use ControllerTestTrait;
    use ApiControllerTestTrait;

    private const DECLARATION_URL = '/api/v3/profile/elect-declaration';
    private const PAYMENT_URL = '/api/v3/profile/elect-payment';
    private const PAYMENT_STOP_URL = '/api/v3/profile/elect-payment/stop';

    private const PRIMO_COTISANT_EMAIL = 'gisele-berthoux@caramail.com';
    private const ACTIVE_CONTRIBUTION_EMAIL = 'renaissance-user-2@en-marche-dev.fr';

    private ?AdherentRepository $adherentRepository = null;
    private ?ContributionRepository $contributionRepository = null;
    private ?DummyClient $gocardlessClient = null;

    public function testFirstDeclarationEligibleReturnsPaymentStepRequiredTrue(): void
    {
        $accessToken = $this->authenticateAs(self::PRIMO_COTISANT_EMAIL);

        $this->postDeclaration($accessToken, 2500);

        $this->assertResponseStatusCode(Response::HTTP_CREATED, $this->client->getResponse());

        $payload = $this->decodeResponse();
        self::assertSame('ok', $payload['status']);
        self::assertTrue($payload['payment_step_required']);
        self::assertSame(50, $payload['current_contribution_amount']);

        $adherent = $this->findAdherent(self::PRIMO_COTISANT_EMAIL);
        self::assertCount(1, $adherent->getRevenueDeclarations());
        self::assertSame(ContributionStatusEnum::ELIGIBLE, $adherent->getContributionStatus());
        self::assertNull($adherent->getLastContribution());
    }

    public function testFirstDeclarationNotEligibleReturnsPaymentStepRequiredFalse(): void
    {
        $accessToken = $this->authenticateAs(self::PRIMO_COTISANT_EMAIL);

        $this->postDeclaration($accessToken, 100);

        $this->assertResponseStatusCode(Response::HTTP_CREATED, $this->client->getResponse());

        $payload = $this->decodeResponse();
        self::assertFalse($payload['payment_step_required']);
        self::assertSame(0, $payload['current_contribution_amount']);

        $adherent = $this->findAdherent(self::PRIMO_COTISANT_EMAIL);
        self::assertSame(ContributionStatusEnum::NOT_ELIGIBLE, $adherent->getContributionStatus());
        self::assertNotNull($adherent->getContributedAt());
        self::assertNull($adherent->getLastContribution());
    }

    public function testRedeclarationToNotEligibleCancelsLastContribution(): void
    {
        $accessToken = $this->authenticateAs(self::ACTIVE_CONTRIBUTION_EMAIL);

        $this->postDeclaration($accessToken, 100);

        $this->assertResponseStatusCode(Response::HTTP_CREATED, $this->client->getResponse());

        $payload = $this->decodeResponse();
        self::assertFalse($payload['payment_step_required']);
        self::assertSame(0, $payload['current_contribution_amount']);

        $fixtureContribution = $this->findContributionByUuid(LoadContributionData::CONTRIBUTION_01_UUID);
        self::assertSame('cancelled', $fixtureContribution->gocardlessSubscriptionStatus);
        self::assertSame('cancelled', $fixtureContribution->gocardlessMandateStatus);
        self::assertFalse($fixtureContribution->gocardlessBankAccountEnabled);

        $adherent = $this->findAdherent(self::ACTIVE_CONTRIBUTION_EMAIL);
        self::assertNotNull($adherent->getContributedAt());
    }

    public function testRedeclarationWithSameExpectedAmountIsNoop(): void
    {
        $accessToken = $this->authenticateAs(self::ACTIVE_CONTRIBUTION_EMAIL);

        // Fixture revenue is 10 000€ → capped at 200€/month. 12 000€ still → 200€/month.
        $this->postDeclaration($accessToken, 12000);

        $this->assertResponseStatusCode(Response::HTTP_CREATED, $this->client->getResponse());

        $payload = $this->decodeResponse();
        self::assertFalse($payload['payment_step_required']);
        self::assertSame(200, $payload['current_contribution_amount']);

        // Old Contribution remains untouched — no soft cancel triggered.
        $fixtureContribution = $this->findContributionByUuid(LoadContributionData::CONTRIBUTION_01_UUID);
        self::assertSame('active', $fixtureContribution->gocardlessSubscriptionStatus);
        self::assertSame('SB_DPT82', $fixtureContribution->gocardlessSubscriptionId);

        // Only the fixture Contribution exists — no new one created.
        $adherent = $this->findAdherent(self::ACTIVE_CONTRIBUTION_EMAIL);
        self::assertCount(1, $adherent->getContributions());
    }

    public function testRedeclarationWithDifferentAmountUpdatesSubscriptionInPlace(): void
    {
        $this->client->disableReboot();

        $accessToken = $this->authenticateAs(self::ACTIVE_CONTRIBUTION_EMAIL);

        // Reset call log AFTER the oauth request (which may instantiate the DummyClient transitively).
        $this->gocardlessClient = $this->get(DummyClient::class);
        $this->gocardlessClient->calls = [];

        // 2500€ → 50€/month (different from the 200€ cap computed on the fixture's 10 000€).
        $this->postDeclaration($accessToken, 2500);

        $this->assertResponseStatusCode(Response::HTTP_CREATED, $this->client->getResponse());

        $payload = $this->decodeResponse();
        self::assertFalse($payload['payment_step_required']);
        self::assertSame(50, $payload['current_contribution_amount']);

        // GoCardless was asked to update the amount on the EXISTING subscription.
        $updateCalls = $this->gocardlessCallsByMethod('updateSubscriptionAmount');
        self::assertCount(1, $updateCalls);
        self::assertSame('SB_DPT82', $updateCalls[0]['args']['subscriptionId']);
        self::assertSame(50, $updateCalls[0]['args']['amount']);

        // No cancel + create — the mandate and subscription are kept in place.
        self::assertCount(0, $this->gocardlessCallsByMethod('cancelSubscription'));
        self::assertCount(0, $this->gocardlessCallsByMethod('cancelMandate'));
        self::assertCount(0, $this->gocardlessCallsByMethod('createSubscription'));

        // The fixture Contribution row is still the ONE contribution — no duplication in DB.
        $adherent = $this->findAdherent(self::ACTIVE_CONTRIBUTION_EMAIL);
        self::assertCount(1, $adherent->getContributions());

        $lastContribution = $adherent->getLastContribution();
        self::assertNotNull($lastContribution);
        self::assertSame('SB_DPT82', $lastContribution->gocardlessSubscriptionId);
        self::assertSame('MD_DPT92', $lastContribution->gocardlessMandateId);
    }

    public function testResponseShapeContainsExpectedFields(): void
    {
        $accessToken = $this->authenticateAs(self::PRIMO_COTISANT_EMAIL);

        $this->postDeclaration($accessToken, 2500);

        $this->assertResponseStatusCode(Response::HTTP_CREATED, $this->client->getResponse());

        $payload = $this->decodeResponse();
        self::assertSame(
            ['status', 'payment_step_required', 'current_contribution_amount'],
            array_keys($payload),
        );
    }

    public function testSavePaymentStillWorksAsBefore(): void
    {
        $accessToken = $this->authenticateAs(self::PRIMO_COTISANT_EMAIL);

        // Prerequisite: the declaration must exist before calling /elect-payment.
        $this->postDeclaration($accessToken, 2500);
        $this->assertResponseStatusCode(Response::HTTP_CREATED, $this->client->getResponse());

        $this->client->request(Request::METHOD_POST, self::PAYMENT_URL, [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => "Bearer $accessToken",
        ], json_encode([
            'accountName' => 'Gisele Berthoux',
            'accountCountry' => 'FR',
            'iban' => 'FR7630056009271234567890182',
        ], \JSON_THROW_ON_ERROR));

        $this->assertResponseStatusCode(Response::HTTP_CREATED, $this->client->getResponse());

        $adherent = $this->findAdherent(self::PRIMO_COTISANT_EMAIL);
        self::assertNotNull($adherent->getLastContribution());
        self::assertSame('SB0123456', $adherent->getLastContribution()->gocardlessSubscriptionId);
    }

    public function testStopPaymentCancelsAndPersistsContribution(): void
    {
        $accessToken = $this->authenticateAs(self::ACTIVE_CONTRIBUTION_EMAIL);

        $this->client->request(Request::METHOD_POST, self::PAYMENT_STOP_URL, [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => "Bearer $accessToken",
        ]);

        $this->assertResponseStatusCode(Response::HTTP_CREATED, $this->client->getResponse());

        $fixtureContribution = $this->findContributionByUuid(LoadContributionData::CONTRIBUTION_01_UUID);
        self::assertSame('cancelled', $fixtureContribution->gocardlessSubscriptionStatus);
        self::assertSame('cancelled', $fixtureContribution->gocardlessMandateStatus);
        self::assertFalse($fixtureContribution->gocardlessBankAccountEnabled);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->adherentRepository = $this->getAdherentRepository();
        $this->contributionRepository = $this->get(ContributionRepository::class);

        $this->gocardlessClient = $this->get(DummyClient::class);
        self::assertInstanceOf(DummyClient::class, $this->gocardlessClient);
        $this->gocardlessClient->calls = [];
    }

    protected function tearDown(): void
    {
        $this->adherentRepository = null;
        $this->contributionRepository = null;
        $this->gocardlessClient = null;

        parent::tearDown();
    }

    /**
     * @return list<array{method: string, args: array<string, mixed>}>
     */
    private function gocardlessCallsByMethod(string $method): array
    {
        return array_values(array_filter(
            $this->gocardlessClient->calls,
            static fn (array $call): bool => $call['method'] === $method,
        ));
    }

    private function authenticateAs(string $email): string
    {
        return $this->getAccessToken(
            LoadClientData::CLIENT_10_UUID,
            'MWFod6bOZb2mY3wLE=4THZGbOfHJvRHk8bHdtZP3BTr',
            GrantTypeEnum::PASSWORD,
            Scope::READ_PROFILE.' '.Scope::WRITE_PROFILE,
            $email,
            LoadAdherentData::DEFAULT_PASSWORD,
        );
    }

    private function postDeclaration(string $accessToken, int $revenueAmount): void
    {
        $this->client->request(Request::METHOD_POST, self::DECLARATION_URL, [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => "Bearer $accessToken",
        ], json_encode(['revenueAmount' => $revenueAmount], \JSON_THROW_ON_ERROR));
    }

    private function decodeResponse(): array
    {
        return json_decode($this->client->getResponse()->getContent(), true, 512, \JSON_THROW_ON_ERROR);
    }

    private function findAdherent(string $email): Adherent
    {
        $this->getEntityManager(Adherent::class)->clear();
        $adherent = $this->adherentRepository->findOneByEmail($email);
        self::assertInstanceOf(Adherent::class, $adherent);

        return $adherent;
    }

    private function findContributionByUuid(string $uuid): Contribution
    {
        $this->getEntityManager(Contribution::class)->clear();
        $contribution = $this->contributionRepository->findOneBy(['uuid' => $uuid]);
        self::assertInstanceOf(Contribution::class, $contribution);

        return $contribution;
    }
}
