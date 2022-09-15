<?php

namespace Tests\App\Controller\Renaissance\Adhesion;

use App\Donation\PayboxPaymentSubscription;
use App\Entity\Adherent;
use App\Entity\Donation;
use App\Mailer\Message\AdherentAccountActivationMessage;
use App\Repository\AdherentActivationTokenRepository;
use App\Repository\AdherentRepository;
use App\Repository\DonationRepository;
use App\Repository\EmailRepository;
use Goutte\Client as PayboxClient;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\App\AbstractWebCaseTest as WebTestCase;
use Tests\App\Controller\ControllerTestTrait;
use Tests\App\Test\Payment\PayboxProvider;

/**
 * @group functional
 */
class AdhesionControllerTest extends WebTestCase
{
    use ControllerTestTrait;

    private const PAYBOX_PREPROD_URL = 'https://preprod-tpeweb.paybox.com/cgi/MYchoix_pagepaiement.cgi';

    private ?AdherentRepository $adherentRepository = null;
    private ?AdherentActivationTokenRepository $activationTokenRepository = null;
    private ?DonationRepository $donationRepository = null;
    private ?EmailRepository $emailRepository = null;
    private ?PayboxClient $payboxClient = null;
    private ?PayboxProvider $payboxProvider = null;

    public function testRenaissanceMembershipRequest(): void
    {
        $crawler = $this->client->request(Request::METHOD_GET, '/adhesion');

        $this->assertCount(0, $this->getEmailRepository()->findMessages(AdherentAccountActivationMessage::class));

        // fill personal info
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $this->client->submit($crawler->filter('form[name="app_renaissance_membership"]')->form([
            'g-recaptcha-response' => 'fake',
            'app_renaissance_membership' => [
                'gender' => 'male',
                'firstName' => 'John',
                'lastName' => 'SMITH',
                'address' => [
                    'country' => 'FR',
                    'address' => '62 avenue des Champs-Élysées',
                    'postalCode' => '75008',
                    'city' => '75008-75108',
                    'cityName' => 'Paris 8ème',
                ],
                'password' => 'secret!12345',
                'nationality' => 'FR',
                'emailAddress' => [
                    'first' => 'john@test.com',
                    'second' => 'john@test.com',
                ],
                'birthdate' => [
                    'day' => 1,
                    'month' => 1,
                    'year' => 1989,
                ],
            ],
        ]));

        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());
        $this->assertClientIsRedirectedTo('/adhesion/contribution', $this->client);

        $crawler = $this->client->followRedirect();

        // choose amount
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->client->submit($crawler->filter('form[name="app_renaissance_membership"]')->form([
            'app_renaissance_membership' => [
                'amount' => 30,
            ],
        ]));

        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());
        $this->assertClientIsRedirectedTo('/adhesion/informations-additionelles', $this->client);

        $crawler = $this->client->followRedirect();

        // additional informations
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->client->submit($crawler->filter('form[name="app_renaissance_membership"]')->form([
            'app_renaissance_membership' => [
                'phone' => [
                    'country' => 'FR',
                    'number' => '0612345678',
                ],
                'position' => 'student',
            ],
        ]));

        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());
        $this->assertClientIsRedirectedTo('/adhesion/mentions', $this->client);

        $crawler = $this->client->followRedirect();

        // mentions
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->client->submit($crawler->filter('form[name="app_renaissance_membership"]')->form([
            'app_renaissance_membership' => [
                'conditions' => true,
                'exclusiveMembership' => true,
                'allowEmailNotifications' => true,
                'allowMobileNotifications' => true,
            ],
        ]));

        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());
        $this->assertClientIsRedirectedTo('/adhesion/recapitulatif', $this->client);

        $crawler = $this->client->followRedirect();

        // summary
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $list = $crawler->filter('.py-10 > div');
        $this->assertStringContainsString('John', $list->eq(0)->text());
        $this->assertStringContainsString('Smith', $list->eq(1)->text());
        $this->assertStringContainsString('62 avenue des Champs-Élysées, 75008 Paris 8ème, FR', $list->eq(2)->text());
        $this->assertStringContainsString('john@test.com', $list->eq(3)->text());
        $this->assertStringContainsString('1 janvier 1989', $list->eq(4)->text());
        $this->assertStringContainsString('Homme', $list->eq(5)->text());
        $this->assertStringContainsString('France', $list->eq(6)->text());
        $this->assertStringContainsString('Téléphone', $list->eq(7)->text());
        $this->assertStringContainsString('30 €', $list->eq(8)->text());

        $this->client->submit($crawler->selectButton('Procéder au paiement')->form());

        $this->assertStatusCode(Response::HTTP_FOUND, $this->client);
        $this->assertInstanceOf(Adherent::class, $adherent = $this->adherentRepository->findOneByEmail('john@test.com'));

        $this->assertInstanceOf(Donation::class, $donation = $this->donationRepository->findInProgressMembershipDonationFromAdherent($adherent));
        $this->assertEquals(3000, $donation->getAmount());
        $this->assertSame('male', $donation->getDonator()->getGender());
        $this->assertSame('Smith', $donation->getDonator()->getLastName());
        $this->assertSame('John', $donation->getDonator()->getFirstName());
        $this->assertSame('john@test.com', $donation->getDonator()->getEmailAddress());

        $this->assertCount(0, $this->getEmailRepository()->findMessages(AdherentAccountActivationMessage::class));

        $this->assertClientIsRedirectedTo(sprintf('/adhesion/%s/paiement', $donation->getUuid()->toString()), $this->client);

        $crawler = $this->client->followRedirect();

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $formNode = $crawler->filter('input[name=PBX_CMD]');

        if ($suffix = PayboxPaymentSubscription::getCommandSuffix($donation->getAmount(), $donation->getDuration())) {
            $this->assertStringContainsString($suffix, $formNode->attr('value'));
        }

        $formNode = $crawler->filter('form[name=app_adhesion_payment]');

        $this->assertSame(self::PAYBOX_PREPROD_URL, $formNode->attr('action'));

        $crawler = $this->payboxClient->submit($formNode->form());

        if (Response::HTTP_OK !== $status = $this->payboxClient->getInternalResponse()->getStatus()) {
            $this->markTestSkipped(sprintf('Paybox preproduction server has responded with %d.', $status));
        }

        $crawler = $this->payboxClient->submit($crawler->filter('form[name=PAYBOX]')->form());
        $crawler = $this->payboxClient->submit($crawler->filter('form[name=form_pay]')->form([
            'NUMERO_CARTE' => '1111222233334444',
            'MOIS_VALIDITE' => '12',
            'AN_VALIDITE' => '32',
            'CVVX' => '123',
        ]));

        $this->payboxClient->submit($crawler->filter('form[name=form3dsecure]')->form());

        $crawler = $this->payboxClient->clickLink('Continuer');

        $callbackUrl = $crawler->filter('td#ticketCell div.textCenter a')->attr('href');
        $callbackUrlRegExp = 'http://'.$this->getParameter('renaissance_host').'/adhesion/callback/(.+)'; // token
        $callbackUrlRegExp .= '\?id=(.+)_john-smith';
        $callbackUrlRegExp .= '&authorization=XXXXXX&result=00000';
        $callbackUrlRegExp .= '&transaction=(\d+)&amount=3000&date=(\d+)&time=(.+)';
        $callbackUrlRegExp .= '&card_type=(CB|Visa|MasterCard)&card_end=3212&card_print=(.+)&subscription=(\d+)&Sign=(.+)';

        $this->assertMatchesRegularExpression('#'.$callbackUrlRegExp.'#', $callbackUrl);
        $this->client->request(Request::METHOD_GET, $callbackUrl);
        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());
        $this->client->followRedirect();

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $this->client->setServerParameter('HTTP_HOST', $this->getParameter('app_host'));
        self::assertSame('OK', $this->simulateIpnCall($donation, '00000'));

        $this->assertCount(1, $this->getEmailRepository()->findMessages(AdherentAccountActivationMessage::class));
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->client->setServerParameter('HTTP_HOST', $this->getParameter('renaissance_host'));
        $this->adherentRepository = $this->getAdherentRepository();
        $this->activationTokenRepository = $this->getActivationTokenRepository();
        $this->donationRepository = $this->getDonationRepository();
        $this->emailRepository = $this->getEmailRepository();
        $this->payboxClient = new PayboxClient();
        $this->payboxProvider = $this->get(PayboxProvider::class);
    }

    protected function tearDown(): void
    {
        $this->emailRepository = null;
        $this->activationTokenRepository = null;
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
