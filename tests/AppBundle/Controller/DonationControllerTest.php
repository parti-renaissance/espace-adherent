<?php

namespace Tests\AppBundle\Controller;

use AppBundle\Entity\Donation;
use AppBundle\Repository\DonationRepository;
use Symfony\Bundle\FrameworkBundle\Client;
use Goutte\Client as PayboxClient;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Tests\AppBundle\TestHelperTrait;

class DonationControllerTest extends WebTestCase
{
    use TestHelperTrait;

    /* @var Client */
    private $appClient;

    /* @var PayboxClient */
    private $payboxClient;

    /* @var DonationRepository */
    private $donationRepository;

    public function testFullProcess()
    {
        // There should not be any donation for the moment
        $this->assertCount(0, $this->donationRepository->findAll());

        /*
         * Initial questions page
         */
        $crawler = $this->appClient->request('GET', '/don');
        $this->assertSame(200, $this->appClient->getResponse()->getStatusCode());

        $this->appClient->submit($crawler->filter('form[name=app_donation]')->form([
            'app_donation[amount]' => '30',
            'app_donation[gender]' => 'male',
            'app_donation[lastName]' => 'Doe',
            'app_donation[firstName]' => 'John',
            'app_donation[email]' => 'test@paybox.com',
            'app_donation[country]' => 'FR',
            'app_donation[postalCode]' => '10000',
            'app_donation[city]' => '10000-10387',
            'app_donation[address]' => '12 Rue Marie Curie',
            'app_donation[phone][country]' => 'FR',
            'app_donation[phone][number]' => '606060606',
        ]));

        // Donation should have been saved
        $this->assertCount(1, $donations = $this->donationRepository->findAll());
        $this->assertInstanceOf(Donation::class, $donation = $donations[0]);
        $this->assertEquals(30, $donation->getAmount());
        $this->assertSame('male', $donation->getGender());
        $this->assertSame('Doe', $donation->getLastName());
        $this->assertSame('John', $donation->getFirstName());
        $this->assertSame('test@paybox.com', $donation->getEmail());
        $this->assertSame('FR', $donation->getCountry());
        $this->assertSame('10000', $donation->getPostalCode());
        $this->assertSame('10000-10387', $donation->getCity());
        $this->assertSame('12 Rue Marie Curie', $donation->getAddress());
        $this->assertSame(33, $donation->getPhone()->getCountryCode());
        $this->assertSame('606060606', $donation->getPhone()->getNationalNumber());

        // We should be redirected to payment
        $this->assertEquals(302, $this->appClient->getResponse()->getStatusCode());

        $this->appClient->followRedirect();
        $this->assertSame(200, $this->appClient->getResponse()->getStatusCode());

        /*
         * En-Marche payment page (verification and form to Paybox)
         */
        $formNode = $this->appClient->getCrawler()->filter('form[name=app_donation_payment]');
        $this->assertSame('https://preprod-tpeweb.paybox.com/cgi/MYchoix_pagepaiement.cgi', $formNode->attr('action'));

        $formTime = time();
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
        $this->assertSame(1, $crawler->filter('td:contains("30.00 EUR")')->count());
        $this->assertContains('Paiement r&eacute;alis&eacute; avec succ&egrave;s', $content);

        /*
         * Emulate IPN callback to Symfony
         */
        $mockUrl = $crawler->filter('a')->first()->attr('href');
        $ipnUrl = str_replace('https://httpbin.org/status/200', '/don/payment-ipn/'.$formTime, $mockUrl);

        $this->appClient->request('GET', $ipnUrl);
        $this->assertSame(200, $this->appClient->getResponse()->getStatusCode());

        // Donation should have been completed
        $this->getEntityManager(Donation::class)->refresh($donation);

        $this->assertTrue($donation->isFinished());
        $this->assertNotNull($donation->getDonatedAt());
        $this->assertSame('00000', $donation->getPayboxResultCode());
        $this->assertSame('XXXXXX', $donation->getPayboxAuthorizationCode());

        /*
         * Check callback redirect to success page
         */
        $callbackUrl = str_replace('https://httpbin.org/status/200', '/don/callback', $mockUrl);

        // $symfonyClient->request('GET', $callbackUrl);
        // $this->assertEquals(302, $symfonyClient->getResponse()->getStatusCode());
    }

    protected function setUp()
    {
        parent::setUp();

        $this->appClient = static::createClient();
        $this->payboxClient = new PayboxClient();
        $this->container = $this->appClient->getContainer();
        $this->donationRepository = $this->getDonationRepository();
    }

    protected function tearDown()
    {
        $this->payboxClient = new PayboxClient();
        $this->donationRepository = null;
        $this->container = null;
        $this->appClient = null;

        parent::tearDown();
    }
}
