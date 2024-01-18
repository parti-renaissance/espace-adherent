<?php

namespace Tests\App\Controller\Renaissance\Adhesion;

use App\Donation\Paybox\PayboxPaymentSubscription;
use App\Entity\Adherent;
use App\Entity\Donation;
use App\Entity\Renaissance\Adhesion\AdherentRequest;
use App\Mailer\Message\AdherentAccountActivationMessage;
use App\Mailer\Message\Renaissance\RenaissanceAdherentAccountActivationMessage;
use App\Mailer\Message\Renaissance\RenaissanceAdherentAccountConfirmationMessage;
use App\Repository\AdherentRepository;
use App\Repository\DonationRepository;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\App\AbstractRenaissanceWebTestCase;
use Tests\App\Controller\ControllerTestTrait;
use Tests\App\Test\Payment\PayboxProvider;

#[Group('functional')]
class AdhesionControllerTest extends AbstractRenaissanceWebTestCase
{
    use ControllerTestTrait;

    private const PAYBOX_PREPROD_URL = 'https://preprod-tpeweb.paybox.com/cgi/MYchoix_pagepaiement.cgi';

    private ?AdherentRepository $adherentRepository = null;
    private ?DonationRepository $donationRepository = null;
    private ?HttpBrowser $payboxClient = null;
    private ?PayboxProvider $payboxProvider = null;

    public function testRenaissanceMembershipRequest(): void
    {
        $crawler = $this->client->request(Request::METHOD_GET, '/v1/adhesion?utm_source=test&utm_campaign=let_CI_be_green_again');

        $this->assertCount(0, $this->getEmailRepository()->findMessages(AdherentAccountActivationMessage::class));

        // fill personal info
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $this->client->submit($crawler->filter('form[name="app_renaissance_membership"]')->form([
            'frc-captcha-solution' => 'fake',
            'app_renaissance_membership' => [
                'firstName' => 'John',
                'lastName' => 'SMITH',
                'address' => [
                    'country' => 'FR',
                    'address' => '62 avenue des Champs-Élysées',
                    'postalCode' => '75008',
                    'cityName' => 'Paris 8ème',
                ],
                'password' => 'secret!12345',
                'emailAddress' => [
                    'first' => 'john@test.com',
                    'second' => 'john@test.com',
                ],
            ],
        ]));

        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());
        $this->assertClientIsRedirectedTo('/v1/adhesion/cotisation', $this->client);

        $crawler = $this->client->followRedirect();

        // choose amount
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $form = $crawler->filter('form[name="app_renaissance_membership"]')->form([
            'app_renaissance_membership' => [
                'amount' => 9,
            ],
        ]);

        $form['app_renaissance_membership[isPhysicalPerson]']->tick();
        $form['app_renaissance_membership[conditions]']->tick();
        $form['app_renaissance_membership[cguAccepted]']->tick();

        $crawler = $this->client->submit($form);

        $this->assertStringContainsString('Le montant de la cotisation est invalid', $this->client->getResponse()->getContent());

        $form = $crawler->filter('form[name="app_renaissance_membership"]')->form([
            'app_renaissance_membership' => [
                'amount' => 30.75,
            ],
        ]);

        $form['app_renaissance_membership[isPhysicalPerson]']->tick();
        $form['app_renaissance_membership[conditions]']->tick();
        $form['app_renaissance_membership[cguAccepted]']->tick();

        $this->client->submit($form);

        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());
        $this->assertClientIsRedirectedTo('/v1/adhesion/recapitulatif', $this->client);

        $crawler = $this->client->followRedirect();

        // summary
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $list = $crawler->filter('.my-10 > section > div');
        $this->assertStringContainsString('John', $list->eq(0)->text());
        $this->assertStringContainsString('Smith', $list->eq(1)->text());
        $this->assertStringContainsString('62 avenue des Champs-Élysées, 75008, Paris 8ème, FR', $list->eq(2)->text());
        $this->assertStringContainsString('john@test.com', $list->eq(3)->text());
        $this->assertStringContainsString("30,75\u{a0}€", $list->eq(4)->text());

        $this->client->submit($crawler->selectButton('Confirmer mon email')->form());

        $adherentRequest = $this->getEntityManager()->getRepository(AdherentRequest::class)->findOneBy(['email' => 'john@test.com']);
        $this->assertInstanceOf(AdherentRequest::class, $adherentRequest);

        $this->assertEquals(30.75, $adherentRequest->amount);
        $this->assertSame('Smith', $adherentRequest->lastName);
        $this->assertSame('John', $adherentRequest->firstName);
        $this->assertSame('john@test.com', $adherentRequest->email);
        $this->assertSame('test', $adherentRequest->utmSource);
        $this->assertSame('let_CI_be_green_again', $adherentRequest->utmCampaign);

        $this->assertCount(1, $this->getEmailRepository()->findMessages(RenaissanceAdherentAccountActivationMessage::class));

        $this->client->request(Request::METHOD_GET, sprintf('/v1/adhesion/finaliser/%s/%s', $adherentRequest->getUuid(), $adherentRequest->token));

        $this->assertCount(0, $this->getEmailRepository()->findMessages(RenaissanceAdherentAccountConfirmationMessage::class));

        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());
        $crawler = $this->client->followRedirect();

        $this->assertInstanceOf(Adherent::class, $adherent = $this->adherentRepository->findOneByEmail('john@test.com'));

        $this->assertSame('test', $adherent->utmSource);
        $this->assertSame('let_CI_be_green_again', $adherent->utmCampaign);

        $form = $crawler->filter('form[name="app_renaissance_membership"]')->form([
            'app_renaissance_membership' => [
                'nationality' => 'FR',
                'gender' => 'male',
                'exclusiveMembership' => true,
                'territoireProgresMembership' => false,
                'agirMembership' => false,
            ],
        ]);

        $this->client->submit($form);

        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());

        $this->client->followRedirect();

        $this->assertInstanceOf(Donation::class, $donation = $this->donationRepository->findInProgressMembershipDonationFromAdherent($adherent));

        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());
        $crawler = $this->client->followRedirect();

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $formNode = $crawler->filter('input[name=PBX_CMD]');

        if ($suffix = PayboxPaymentSubscription::getCommandSuffix($donation->getAmount(), $donation->getDuration())) {
            $this->assertStringContainsString($suffix, $formNode->attr('value'));
        }

        $formNode = $crawler->filter('form[name=app_adhesion_payment]');

        $this->assertSame(self::PAYBOX_PREPROD_URL, $formNode->attr('action'));

        $crawler = $this->payboxClient->submit($formNode->form());

        if (Response::HTTP_OK !== $status = $this->payboxClient->getInternalResponse()->getStatusCode()) {
            $this->markTestSkipped(sprintf('Paybox preproduction server has responded with %d.', $status));
        }

        $crawler = $this->payboxClient->submit($crawler->filter('form[name=PAYBOX]')->form());
        $crawler = $this->payboxClient->submit($crawler->filter('form[name=form_pay]')->form([
            'NUMERO_CARTE' => '1111222233334444',
            'MOIS_VALIDITE' => '12',
            'AN_VALIDITE' => '32',
            'CVVX' => '123',
        ]));

        if ($crawler->filter('form[name=form3dsecure]')->count()) {
            $this->payboxClient->submit($crawler->filter('form[name=form3dsecure]')->form());

            $crawler = $this->payboxClient->clickLink('Continuer');
        }

        $callbackUrl = $crawler->filter('td#ticketCell div.textCenter a')->attr('href');
        $callbackUrlRegExp = 'http://'.$this->getParameter('app_renaissance_host').'/v1/adhesion/callback/(.+)'; // token
        $callbackUrlRegExp .= '\?id=(.+)_john-smith';
        $callbackUrlRegExp .= '&authorization=XXXXXX&result=00000';
        $callbackUrlRegExp .= '&transaction=(\d+)&amount=3075&date=(\d+)&time=(.+)';
        $callbackUrlRegExp .= '&card_type=(CB|Visa|MasterCard)&card_end=3212&card_print=(.+)&subscription=(\d+)&Sign=(.+)';

        $this->assertMatchesRegularExpression('#'.$callbackUrlRegExp.'#', $callbackUrl);
        $this->client->request(Request::METHOD_GET, $callbackUrl);
        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());
        $this->client->followRedirect();

        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());
        $crawler = $this->client->followRedirect();

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        self::assertSame('OK', $this->simulateIpnCall($donation, '00000'));

        $this->assertCount(1, $this->getEmailRepository()->findMessages(RenaissanceAdherentAccountActivationMessage::class));

        $form = $crawler->filter('form[name="app_renaissance_membership"]')->form([
            'app_renaissance_membership' => [
                'birthdate' => [
                    'day' => 1,
                    'month' => 1,
                    'year' => 1989,
                ],
                'phone' => [
                    'country' => 'FR',
                    'number' => '0612345678',
                ],
            ],
        ]);

        $this->client->submit($form);

        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());
        $this->assertClientIsRedirectedTo('/v1/adhesion/fin', $this->client);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->adherentRepository = $this->getAdherentRepository();
        $this->donationRepository = $this->getDonationRepository();
        $this->payboxClient = new HttpBrowser();
        $this->payboxProvider = $this->get(PayboxProvider::class);
    }

    protected function tearDown(): void
    {
        $this->adherentRepository = null;
        $this->donationRepository = null;
        $this->payboxClient = null;
        $this->payboxProvider = null;

        parent::tearDown();
    }

    private function simulateIpnCall(Donation $donation, string $status): string
    {
        return $this->client
            ->request(
                'POST',
                $this->payboxProvider->getIpnUri(),
                $this->payboxProvider->prepareCallbackParameters($donation->getUuid()->toString(), $status)
            )
            ->text()
        ;
    }
}
