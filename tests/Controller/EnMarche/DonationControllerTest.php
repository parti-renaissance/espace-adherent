<?php

namespace Tests\AppBundle\Controller\EnMarche;

use AppBundle\Donation\PayboxPaymentSubscription;
use AppBundle\Entity\Donation;
use AppBundle\Mailer\Message\DonationMessage;
use AppBundle\Repository\DonationRepository;
use Goutte\Client as PayboxClient;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\AppBundle\Controller\ControllerTestTrait;
use Tests\AppBundle\SqliteWebTestCase;

/**
 * @group functional
 * @group donation
 */
class DonationControllerTest extends SqliteWebTestCase
{
    use ControllerTestTrait;

    /* @var PayboxClient */
    private $payboxClient;

    /* @var DonationRepository */
    private $donationRepository;

    public function getDonationSubscriptions(): iterable
    {
        foreach (PayboxPaymentSubscription::DURATIONS as $test => $duration) {
            yield $test => [$duration];
        }

        yield 'None' => [PayboxPaymentSubscription::NONE];
    }

    /**
     * @dataProvider getDonationSubscriptions
     */
    public function testSuccessFulProcess(int $duration)
    {
        $appClient = $this->client;
        // There should not be any donation for the moment
        $this->assertCount(0, $this->donationRepository->findAll());

        $crawler = $appClient->request(Request::METHOD_GET, sprintf('/don/coordonnees?montant=30&abonnement=%d', $duration));

        $this->assertResponseStatusCode(Response::HTTP_OK, $appClient->getResponse());

        $this->client->submit($crawler->filter('form[name=app_donation]')->form([
            'app_donation' => [
                'gender' => 'male',
                'lastName' => 'Doe',
                'firstName' => 'John',
                'emailAddress' => 'test@paybox.com',
                'address' => '9 rue du Lycée',
                'country' => 'FR',
                'postalCode' => '06000',
                'cityName' => 'Nice',
                'phone' => [
                    'country' => 'FR',
                    'number' => '04 01 02 03 04',
                ],
            ],
        ]));

        // Donation should have been saved
        $this->assertCount(1, $donations = $this->donationRepository->findAll());
        $this->assertInstanceOf(Donation::class, $donation = $donations[0]);

        /* @var Donation $donation */
        $this->assertEquals(3000, $donation->getAmount());
        $this->assertSame('male', $donation->getGender());
        $this->assertSame('Doe', $donation->getLastName());
        $this->assertSame('John', $donation->getFirstName());
        $this->assertSame('test@paybox.com', $donation->getEmailAddress());
        $this->assertSame('FR', $donation->getCountry());
        $this->assertSame('06000', $donation->getPostalCode());
        $this->assertSame('Nice', $donation->getCityName());
        $this->assertSame('9 rue du Lycée', $donation->getAddress());
        $this->assertSame(33, $donation->getPhone()->getCountryCode());
        $this->assertSame('401020304', $donation->getPhone()->getNationalNumber());
        $this->assertSame($duration, $donation->getDuration());

        // Email should not have been sent
        $this->assertCount(0, $this->getEmailRepository()->findMessages(DonationMessage::class));

        // We should be redirected to payment
        $this->assertClientIsRedirectedTo(sprintf('/don/%s/paiement', $donation->getUuid()->toString()), $appClient);

        $crawler = $appClient->followRedirect();

        $this->assertResponseStatusCode(Response::HTTP_OK, $appClient->getResponse());

        $formNode = $crawler->filter('input[name=PBX_CMD]');

        if ($suffix = PayboxPaymentSubscription::getCommandSuffix($donation->getAmount(), $donation->getDuration())) {
            $this->assertContains($suffix, $formNode->attr('value'));
        }

        /*
         * En-Marche payment page (verification and form to Paybox)
         */
        $formNode = $crawler->filter('form[name=app_donation_payment]');

        $this->assertSame('https://preprod-tpeweb.paybox.com/cgi/MYchoix_pagepaiement.cgi', $formNode->attr('action'));

        $crawler = $this->payboxClient->submit($formNode->form());

        /*
         * Paybox redirection and payment form
         */
        $crawler = $this->payboxClient->submit($crawler->filter('form[name=PAYBOX]')->form());

        // Pay using a testing account
        $crawler = $this->payboxClient->submit($crawler->filter('form[name=form_pay]')->form([
            'NUMERO_CARTE' => '1111222233334444',
            'MOIS_VALIDITE' => '12',
            'AN_VALIDITE' => '32',
            'CVVX' => '123',
        ]));

        $content = $this->payboxClient->getInternalResponse()->getContent();

        // Check payment was successful
        $expectedCount = $donation->hasSubscription() ? 2 : 1;
        $this->assertSame($expectedCount, $crawler->filter('td:contains("30.00 EUR")')->count());
        $this->assertRegexp('#Paiement r&eacute;alis&eacute; avec succ&egrave;s|PAIEMENT ACCEPT&Eacute;#', $content);

        $callbackUrl = $crawler->filter('a')->attr('href');
        $callbackUrlRegExp = 'http://'.$this->hosts['app'].'/don/callback/(.+)'; // token
        $callbackUrlRegExp .= '\?id=(.+)_john-doe';
        if (PayboxPaymentSubscription::NONE !== $duration) {
            $durationRegExp = $duration < 0 ? 0 : $duration - 1;
            $callbackUrlRegExp .= 'PBX_2MONT0000003000PBX_NBPAIE0'.$durationRegExp.'PBX_FREQ01PBX_QUAND00';
        }
        $callbackUrlRegExp .= '&authorization=XXXXXX&result=00000';
        $callbackUrlRegExp .= '&transaction=(\d+)&amount=3000&date=(\d+)&time=(.+)';
        $callbackUrlRegExp .= '&card_type=(CB|Visa)&card_end=3212&card_print=(.+)&Sign=(.+)';

        $this->assertRegExp('#'.$callbackUrlRegExp.'#', $callbackUrl);

        $appClient->request(Request::METHOD_GET, $callbackUrl);

        $this->assertResponseStatusCode(Response::HTTP_FOUND, $appClient->getResponse());

        $statusUrl = $appClient->getResponse()->headers->get('location');
        $statusUrlRegExp = '/don/(.+)'; // uuid
        $statusUrlRegExp .= '/effectue\?code=donation_paybox_success&_status_token=(.+)';

        $this->assertRegExp('#'.$statusUrlRegExp.'#', $statusUrl);

        $appClient->followRedirect();

        $this->assertResponseStatusCode(Response::HTTP_OK, $appClient->getResponse());

        // Donation should have been completed
        $this->getEntityManager(Donation::class)->refresh($donation);

        $this->assertTrue($donation->isFinished());
        $this->assertNotNull($donation->getDonatedAt());
        $this->assertSame('00000', $donation->getPayboxResultCode());
        $this->assertSame('XXXXXX', $donation->getPayboxAuthorizationCode());

        // Email should have been sent
        $this->assertCount(1, $this->getEmailRepository()->findMessages(DonationMessage::class));
    }

    /**
     * @dataProvider getDonationSubscriptions
     */
    public function testRetryProcess(int $duration)
    {
        $appClient = $this->client;
        // There should not be any donation for the moment
        $this->assertCount(0, $this->donationRepository->findAll());

        $crawler = $appClient->request(Request::METHOD_GET, sprintf('/don/coordonnees?montant=30&abonnement=%d', $duration));

        $this->assertResponseStatusCode(Response::HTTP_OK, $appClient->getResponse());

        $this->client->submit($crawler->filter('form[name=app_donation]')->form([
            'app_donation' => [
                'gender' => 'male',
                'lastName' => 'Doe',
                'firstName' => 'John',
                'emailAddress' => 'test@paybox.com',
                'address' => '9 rue du Lycée',
                'country' => 'FR',
                'postalCode' => '06000',
                'cityName' => 'Nice',
                'phone' => [
                    'country' => 'FR',
                    'number' => '04 01 02 03 04',
                ],
            ],
        ]));

        // Donation should have been saved
        $this->assertCount(1, $donations = $this->donationRepository->findAll());
        $this->assertInstanceOf(Donation::class, $donation = $donations[0]);

        /* @var Donation $donation */
        $this->assertEquals(3000, $donation->getAmount());
        $this->assertSame('male', $donation->getGender());
        $this->assertSame('Doe', $donation->getLastName());
        $this->assertSame('John', $donation->getFirstName());
        $this->assertSame('test@paybox.com', $donation->getEmailAddress());
        $this->assertSame('FR', $donation->getCountry());
        $this->assertSame('06000', $donation->getPostalCode());
        $this->assertSame('Nice', $donation->getCityName());
        $this->assertSame('9 rue du Lycée', $donation->getAddress());
        $this->assertSame(33, $donation->getPhone()->getCountryCode());
        $this->assertSame('401020304', $donation->getPhone()->getNationalNumber());
        $this->assertSame($duration, $donation->getDuration());

        // Email should not have been sent
        $this->assertCount(0, $this->getEmailRepository()->findMessages(DonationMessage::class));

        // We should be redirected to payment
        $this->assertClientIsRedirectedTo(sprintf('/don/%s/paiement', $donation->getUuid()->toString()), $appClient);

        $crawler = $appClient->followRedirect();

        $this->assertResponseStatusCode(Response::HTTP_OK, $appClient->getResponse());

        $formNode = $crawler->filter('input[name=PBX_CMD]');

        if ($suffix = PayboxPaymentSubscription::getCommandSuffix($donation->getAmount(), $donation->getDuration())) {
            $this->assertContains($suffix, $formNode->attr('value'));
        }

        /*
         * En-Marche payment page (verification and form to Paybox)
         */
        $formNode = $crawler->filter('form[name=app_donation_payment]');

        $this->assertSame('https://preprod-tpeweb.paybox.com/cgi/MYchoix_pagepaiement.cgi', $formNode->attr('action'));

        /*
         * Paybox cancellation of payment form
         */
        $crawler = $this->payboxClient->submit($formNode->form());
        $crawler = $this->payboxClient->submit($crawler->filter('form[name=PAYBOX]')->form());
        $cancelUrl = $crawler->filter('#pbx-annuler a')->attr('href');
        $cancelUrlRegExp = 'http://'.$this->hosts['app'].'/don/callback/(.+)'; // token
        $cancelUrlRegExp .= '\?id=(.+)_john-doe';
        if (PayboxPaymentSubscription::NONE !== $duration) {
            $durationRegExp = $duration < 0 ? 0 : $duration - 1;
            $cancelUrlRegExp .= 'PBX_2MONT0000003000PBX_NBPAIE0'.$durationRegExp.'PBX_FREQ01PBX_QUAND00';
        }
        $cancelUrlRegExp .= '&result=00001'; // error code
        $cancelUrlRegExp .= '&transaction=0&Sign=(.+)';

        $this->assertRegExp('#'.$cancelUrlRegExp.'#', $cancelUrl);

        $appClient->request(Request::METHOD_GET, $cancelUrl);

        $this->assertResponseStatusCode(Response::HTTP_FOUND, $appClient->getResponse());

        $statusUrl = $appClient->getResponse()->headers->get('location');
        $statusUrlRegExp = '/don/(.+)'; // uuid
        $statusUrlRegExp .= '/erreur\?code=paybox&_status_token=(.+)';

        $this->assertRegExp('#'.$statusUrlRegExp.'#', $statusUrl);

        $crawler = $appClient->followRedirect();

        $this->assertResponseStatusCode(Response::HTTP_OK, $appClient->getResponse());

        // Donation should have been aborted
        $this->getEntityManager(Donation::class)->refresh($donation);

        $this->assertTrue($donation->isFinished());
        $this->assertNull($donation->getDonatedAt());
        $this->assertSame('00001', $donation->getPayboxResultCode());
        $this->assertNull($donation->getPayboxAuthorizationCode());

        // Email should not have been sent
        $this->assertCount(0, $this->getEmailRepository()->findMessages(DonationMessage::class));

        $retryUrl = $crawler->selectLink('Je souhaite réessayer')->attr('href');
        $retryUrlRegExp = '/don/coordonnees\?donation_retry_payload=(.*)&montant=30';

        $this->assertRegExp('#'.$retryUrlRegExp.'#', $retryUrl);

        $crawler = $this->client->request(Request::METHOD_GET, $retryUrl);

        $this->assertStatusCode(Response::HTTP_OK, $appClient);
        $this->assertContains('Doe', $crawler->filter('input[name="app_donation[lastName]"]')->attr('value'), 'Retry should be prefilled.');
    }

    public function testCallbackWithNoId()
    {
        $this->client->request(Request::METHOD_GET, '/don/callback/token');

        $this->assertClientIsRedirectedTo('/don', $this->client);
    }

    public function testCallbackWithWrongUuid()
    {
        $this->client->request(Request::METHOD_GET, '/don/callback/token', [
            'id' => 'wrong_uuid',
        ]);

        $this->assertStatusCode(Response::HTTP_FOUND, $this->client);
        $this->assertClientIsRedirectedTo('/don', $this->client);
    }

    public function testCallbackWithWrongToken()
    {
        $crawler = $this->client->request(Request::METHOD_GET, '/don/coordonnees?montant=30');

        $this->client->submit($crawler->filter('form[name=app_donation]')->form([
            'app_donation' => [
                'gender' => 'male',
                'lastName' => 'Doe',
                'firstName' => 'John',
                'emailAddress' => 'test@paybox.com',
                'address' => '9 rue du Lycée',
                'country' => 'FR',
                'postalCode' => '06000',
                'cityName' => 'Nice',
                'phone' => [
                    'country' => 'FR',
                    'number' => '04 01 02 03 04',
                ],
            ],
        ]));

        // Donation should have been saved
        $this->assertCount(1, $donations = $this->donationRepository->findAll());
        $this->assertInstanceOf(Donation::class, $donation = $donations[0]);

        $this->client->request(Request::METHOD_GET, '/don/callback/token', [
            'id' => $donation->getUuid().'_',
        ]);

        $this->assertStatusCode(Response::HTTP_BAD_REQUEST, $this->client);
    }

    protected function setUp()
    {
        parent::setUp();

        $this->init();
        $this->loadFixtures([]);

        $this->payboxClient = new PayboxClient();
        $this->donationRepository = $this->getDonationRepository();
    }

    protected function tearDown()
    {
        $this->kill();

        $this->payboxClient = new PayboxClient();
        $this->donationRepository = null;

        parent::tearDown();
    }
}
