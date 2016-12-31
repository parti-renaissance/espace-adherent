<?php

namespace Tests\AppBundle\Controller;

use AppBundle\Entity\Donation;
use Goutte\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DonationControllerTest extends WebTestCase
{
    public function testFullProcess()
    {
        $symfonyClient = static::createClient();
        $externalClient = new Client();

        $entityManager = $symfonyClient->getContainer()->get('doctrine.orm.entity_manager');
        $donationRepository = $entityManager->getRepository('AppBundle:Donation');

        // There should not be any donation for the moment
        $this->assertEmpty($donationRepository->findAll());

        /*
         * Initial questions page
         */
        $crawler = $symfonyClient->request('GET', '/don');
        $this->assertEquals(200, $symfonyClient->getResponse()->getStatusCode());

        $form = $crawler->filter('form[name=app_donation]')->form([
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
        ]);

        $symfonyClient->submit($form);

        // Donation should have been saved
        $donations = $donationRepository->findAll();
        $this->assertCount(1, $donations);

        /** @var Donation $donation */
        $donation = $donations[0];

        $this->assertEquals(30, $donation->getAmount());
        $this->assertEquals('male', $donation->getGender());
        $this->assertEquals('Doe', $donation->getLastName());
        $this->assertEquals('John', $donation->getFirstName());
        $this->assertEquals('test@paybox.com', $donation->getEmail());
        $this->assertEquals('FR', $donation->getCountry());
        $this->assertEquals('10000', $donation->getPostalCode());
        $this->assertEquals('10000-10387', $donation->getCity());
        $this->assertEquals('12 Rue Marie Curie', $donation->getAddress());
        $this->assertEquals(33, $donation->getPhone()->getCountryCode());
        $this->assertEquals('606060606', $donation->getPhone()->getNationalNumber());

        // We should be redirected to payment
        $this->assertEquals(302, $symfonyClient->getResponse()->getStatusCode());

        $crawler = $symfonyClient->followRedirect();
        $this->assertEquals(200, $symfonyClient->getResponse()->getStatusCode());

        /*
         * En-Marche payment page (verification and form to Paybox)
         */
        $formNode = $crawler->filter('form[name=app_donation_payment]');
        $this->assertEquals('https://preprod-tpeweb.paybox.com/cgi/MYchoix_pagepaiement.cgi', $formNode->attr('action'));

        $formTime = time();
        $form = $formNode->form();
        $crawler = $externalClient->submit($form);

        /*
         * Paybox redirection and payment form
         */
        $crawler = $externalClient->submit($crawler->filter('form[name=PAYBOX]')->form());

        // Pay using a testing account
        $form = $crawler->filter('form[name=form_pay]')->form();
        $form['NUMERO_CARTE'] = '1111222233334444';
        $form['MOIS_VALIDITE'] = '12';
        $form['AN_VALIDITE'] = '32';
        $form['CVVX'] = '123';

        $crawler = $externalClient->submit($form);
        $content = $externalClient->getInternalResponse()->getContent();

        // Check payment was successful
        $this->assertEquals(1, $crawler->filter('td:contains("30.00 EUR")')->count());
        $this->assertContains('Paiement r&eacute;alis&eacute; avec succ&egrave;s', $content);

        /*
         * Emulate IPN callback to Symfony
         */
        $mockUrl = $crawler->filter('a')->first()->attr('href');
        $ipnUrl = str_replace('https://httpbin.org/status/200', '/don/payment-ipn/'.$formTime, $mockUrl);

        $symfonyClient->request('GET', $ipnUrl);
        $this->assertEquals(200, $symfonyClient->getResponse()->getStatusCode());

        // Donation should have been completed
        $entityManager->refresh($donation);

        $this->assertTrue($donation->isFinished());
        $this->assertNotNull($donation->getDonatedAt());
        $this->assertEquals('00000', $donation->getPayboxResultCode());
        $this->assertEquals('XXXXXX', $donation->getPayboxAuthorizationCode());

        /*
         * Check callback redirect to success page
         */
        $callbackUrl = str_replace('https://httpbin.org/status/200', '/don/callback', $mockUrl);

        // $symfonyClient->request('GET', $callbackUrl);
        // $this->assertEquals(302, $symfonyClient->getResponse()->getStatusCode());
    }
}
