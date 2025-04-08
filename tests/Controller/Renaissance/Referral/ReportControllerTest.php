<?php

namespace Controller\Renaissance\Referral;

use App\Adherent\Referral\StatusEnum;
use App\Entity\Referral;
use App\Mailer\Message\Renaissance\Referral\ReferralReportedMessage;
use App\Repository\ReferralRepository;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\App\AbstractWebTestCase;
use Tests\App\Controller\ControllerTestTrait;

#[Group('functional')]
class ReportControllerTest extends AbstractWebTestCase
{
    use ControllerTestTrait;

    private ?ReferralRepository $referralRepository = null;

    #[DataProvider('provideReportableReferralIdentifiers')]
    public function testReferralCanBeReported(string $identifier, string $referrerEmail): void
    {
        $referral = $this->referralRepository->findOneBy(['identifier' => $identifier]);
        $this->assertInstanceOf(Referral::class, $referral);
        $this->assertNotEquals(StatusEnum::REPORTED, $referral->status);
        $this->assertCountMails(0, ReferralReportedMessage::class, $referrerEmail);

        $uuid = $referral->getUuid();
        $crawler = $this->client->request(Request::METHOD_GET, "/invitation/$uuid/signaler");
        $this->assertStatusCode(Response::HTTP_OK, $this->client);
        $this->assertStringContainsString(
            'Signaler cette invitation ?',
            $crawler->text()
        );

        $this->client->submitForm('Signaler');
        $this->assertClientIsRedirectedTo("/invitation/$uuid/signaler", $this->client);

        $crawler = $this->client->followRedirect();
        $this->assertStatusCode(Response::HTTP_OK, $this->client);
        $this->assertStringContainsString(
            'Votre invitation a été signalée',
            $crawler->text()
        );

        $referral = $this->referralRepository->findOneBy(['identifier' => $identifier]);
        $this->assertEquals(StatusEnum::REPORTED, $referral->status);
        $this->assertEmpty($referral->lastName);
        $this->assertEmpty($referral->emailAddress);
        $this->assertEmpty($referral->civility);
        $this->assertEmpty($referral->getPostAddress()->getInlineFormattedAddress());
        $this->assertEmpty($referral->nationality);
        $this->assertEmpty($referral->phone);
        $this->assertEmpty($referral->birthdate);
        $this->assertCountMails(1, ReferralReportedMessage::class, $referrerEmail);
    }

    #[DataProvider('provideAlreadyReportedReferralIdentifiers')]
    public function testReferralIsAlreadyReported(string $referralIdentifier): void
    {
        $referral = $this->referralRepository->findOneBy(['identifier' => $referralIdentifier]);
        $this->assertInstanceOf(Referral::class, $referral);
        $this->assertEquals(StatusEnum::REPORTED, $referral->status);

        $uuid = $referral->getUuid();
        $crawler = $this->client->request(Request::METHOD_GET, "/invitation/$uuid/signaler");
        $this->assertStatusCode(Response::HTTP_OK, $this->client);
        $this->assertStringContainsString(
            'Votre invitation a été signalée',
            $crawler->text()
        );
    }

    public static function provideReportableReferralIdentifiers(): iterable
    {
        yield ['PAB123', 'michelle.dufour@example.ch'];
        yield ['P789YZ', 'michelle.dufour@example.ch'];
    }

    public static function provideAlreadyReportedReferralIdentifiers(): iterable
    {
        yield ['PCD678'];
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->referralRepository = $this->getRepository(Referral::class);

        $this->client->setServerParameter('HTTP_HOST', static::getContainer()->getParameter('user_vox_host'));
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->referralRepository = null;
    }
}
