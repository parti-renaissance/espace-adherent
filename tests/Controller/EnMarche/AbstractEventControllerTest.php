<?php

namespace Tests\AppBundle\Controller\EnMarche;

use AppBundle\Entity\EventRegistration;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Tests\AppBundle\Controller\ControllerTestTrait;

abstract class AbstractEventControllerTest extends WebTestCase
{
    use ControllerTestTrait;
    use RegistrationTrait;

    public function testAnonymousUserCanRegisterToEventWhileLoginIn()
    {
        $eventUrl = $this->getEventUrl();
        $crawler = $this->client->request('GET', $eventUrl);

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $registrationLink = $crawler->filter('.register-event');

        $eventRegistrationUrl = "$eventUrl/inscription";

        $this->assertSame($eventRegistrationUrl, $registrationLink->attr('href'));

        $crawler = $this->client->click($registrationLink->link());

        $this->assertCount(1, $connectLink = $crawler->selectLink('Connectez-vous'));

        $this->client->click($connectLink->link());

        $this->assertClientIsRedirectedTo('/connexion', $this->client);

        $crawler = $this->client->followRedirect();

        $this->client->submit($crawler->selectButton('Connexion')->form([
            '_login_email' => 'benjyd@aol.com',
            '_login_password' => 'secret!12345',
        ]));

        $this->assertClientIsRedirectedTo($eventRegistrationUrl, $this->client);
    }

    public function testAnonymousUserCanRegisterToEventWhileRegistering()
    {
        $eventUrl = $this->getEventUrl();
        $crawler = $this->client->request('GET', $eventUrl);

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $registrationLink = $crawler->filter('.register-event');

        $eventRegistrationUrl = "$eventUrl/inscription";

        $this->assertSame($eventRegistrationUrl, $registrationLink->attr('href'));

        $crawler = $this->client->click($registrationLink->link());

        $this->assertCount(1, $connectLink = $crawler->selectLink('Connectez-vous'));

        $this->client->click($connectLink->link());

        $this->assertClientIsRedirectedTo('/connexion', $this->client);

        $crawler = $this->client->followRedirect();

        $this->assertCount(1, $registerLink = $crawler->selectLink('S’inscrire'));

        $crawler = $this->register($this->client, $this->client->click($registerLink->link()), $eventRegistrationUrl);

        $this->assertSame('Paul', $crawler->filter('#event_registration_firstName')->attr('value'));
        $this->assertSame('Dupont', $crawler->filter('#event_registration_lastName')->attr('value'));
        $this->assertSame('paul@dupont.tld', $crawler->filter('#event_registration_emailAddress')->attr('value'));

        $this->client->submit($crawler->selectButton("Je m'inscris")->form([
            'event_registration[acceptTerms]' => '1',
            'event_registration[personalDataCollection]' => '1',
        ]));

        $this->assertStatusCode(Response::HTTP_FOUND, $this->client);

        $redirectUrl = $this->client->getResponse()->headers->get('location');
        $registration = $this->manager->getRepository(EventRegistration::class)->findOneBy([
            'emailAddress' => 'paul@dupont.tld',
        ]);

        $this->assertInstanceOf(EventRegistration::class, $registration);
        $this->assertSame($eventUrl.'/confirmation?registration='.$registration->getUuid(), $redirectUrl);
    }

    abstract protected function getEventUrl(): string;
}
