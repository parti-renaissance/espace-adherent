<?php

namespace Tests\App\Controller\Renaissance\Referral\Report;

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
class FormControllerTest extends AbstractWebTestCase
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

        $crawler = $this->client->request(Request::METHOD_GET, "/invitation/$identifier/signaler");
        $this->assertStatusCode(Response::HTTP_OK, $this->client);
        $this->assertStringContainsString(
            'Voulez-vous vraiment signaler ce parrainage ?',
            $crawler->text()
        );

        $this->client->submitForm('Oui');
        $this->assertClientIsRedirectedTo('/referral/report/confirmation', $this->client);

        $crawler = $this->client->followRedirect();
        $this->assertStatusCode(Response::HTTP_OK, $this->client);
        $this->assertStringContainsString(
            'Votre signalement a bien été pris en compte.',
            $crawler->text()
        );

        $referral = $this->referralRepository->findOneBy(['identifier' => $identifier]);
        $this->assertEquals(StatusEnum::REPORTED, $referral->status);
        $this->assertCountMails(1, ReferralReportedMessage::class, $referrerEmail);
    }

    #[DataProvider('provideAlreadyReportedReferralIdentifiers')]
    public function testReferralIsAlreadyReported(string $referralIdentifier): void
    {
        $referral = $this->referralRepository->findOneBy(['identifier' => $referralIdentifier]);
        $this->assertInstanceOf(Referral::class, $referral);
        $this->assertEquals(StatusEnum::REPORTED, $referral->status);

        $crawler = $this->client->request(Request::METHOD_GET, "/invitation/$referralIdentifier/signaler");
        $this->assertStatusCode(Response::HTTP_OK, $this->client);
        $this->assertStringContainsString(
            'Vous avez déjà signalé cette adresse email.',
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
