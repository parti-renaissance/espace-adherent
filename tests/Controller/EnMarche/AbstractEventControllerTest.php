<?php

namespace Tests\AppBundle\Controller\EnMarche;

use AppBundle\Entity\AdherentActivationToken;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\AppBundle\Controller\ControllerTestTrait;
use Tests\AppBundle\MysqlWebTestCase;

abstract class AbstractEventControllerTest extends MysqlWebTestCase
{
    use ControllerTestTrait;

    public function testAnonymousUserCanRegisterToEventWhileLoginIn()
    {
        $eventUrl = $this->getEventUrl();
        $crawler = $this->client->request('GET', $eventUrl);

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertSame('1 inscrit', trim($crawler->filter('.event-attendees')->text()));

        $registrationLink = $crawler->filter('.register-event');

        $eventRegistrationUrl = "$eventUrl/inscription";

        $this->assertSame($eventRegistrationUrl, $registrationLink->attr('href'));

        $crawler = $this->client->click($registrationLink->link());

        $this->assertCount(1, $connectLink = $crawler->selectLink('Connectez-vous'));

        $this->client->click($connectLink->link());

        $this->assertClientIsRedirectedTo('/connexion', $this->client);

        $crawler = $this->client->followRedirect();

        $this->client->submit($crawler->selectButton('Connexion')->form([
            '_adherent_email' => 'benjyd@aol.com',
            '_adherent_password' => 'HipHipHip',
        ]));

        $this->assertClientIsRedirectedTo($eventRegistrationUrl, $this->client);
    }

    public function testAnonymousUserCanRegisterToEventWhileRegistering()
    {
        $eventUrl = $this->getEventUrl();
        $crawler = $this->client->request('GET', $eventUrl);

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertSame('1 inscrit', trim($crawler->filter('.event-attendees')->text()));

        $registrationLink = $crawler->filter('.register-event');

        $eventRegistrationUrl = "$eventUrl/inscription";

        $this->assertSame($eventRegistrationUrl, $registrationLink->attr('href'));

        $crawler = $this->client->click($registrationLink->link());

        $this->assertCount(1, $connectLink = $crawler->selectLink('Connectez-vous'));

        $this->client->click($connectLink->link());

        $this->assertClientIsRedirectedTo('/connexion', $this->client);

        $crawler = $this->client->followRedirect();

        //file_put_contents(__DIR__.'/../../../web/heah.html', $this->client->getResponse()->getContent());
        $this->assertCount(1, $registerLink = $crawler->selectLink('S’inscrire'));

        $crawler = $this->client->click($registerLink->link());

        $this->client->submit($crawler->selectButton('Créer mon compte')->form([
            'g-recaptcha-response' => 'dummy',
            'new_member_ship_request' => [
                'firstName' => 'Paul',
                'lastName' => 'Dupont',
                'emailAddress' => [
                    'first' => 'paul@dupont.tld',
                    'second' => 'paul@dupont.tld',
                ],
                'address' => ['postalCode' => '75008'],
                'password' => '#example!12345#',
                'comEmail' => true,
            ],
        ]));

        $this->assertClientIsRedirectedTo('/presque-fini', $this->client);

        $tokens = $this->getRepository(AdherentActivationToken::class)->findAll();

        /** @var AdherentActivationToken $lastActivationToken */
        $this->assertInstanceOf(AdherentActivationToken::class, $lastActivationToken = end($tokens));

        $this->client->request(Request::METHOD_GET, sprintf('/inscription/finaliser/%s/%s', $lastActivationToken->getAdherentUuid(), $lastActivationToken->getValue()));

        $this->assertClientIsRedirectedTo('/adhesion', $this->client);

        $this->client->followRedirect();

        $this->assertClientIsRedirectedTo($eventRegistrationUrl, $this->client);

        $crawler = $this->client->followRedirect();

        $this->assertSame('Paul', $crawler->filter('#event_registration_firstName')->attr('value'));
        $this->assertSame('Dupont', $crawler->filter('#event_registration_lastName')->attr('value'));
        $this->assertSame('paul@dupont.tld', $crawler->filter('#event_registration_emailAddress')->attr('value'));

        $this->client->submit($crawler->selectButton("Je m'inscris")->form([
            'event_registration[acceptTerms]' => '1',
        ]));

        $this->assertClientIsRedirectedTo('/adhesion', $this->client);
    }

    abstract protected function getEventUrl(): string;
}
