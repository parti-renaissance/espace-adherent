<?php

namespace Tests\AppBundle\Controller\EnMarche;

use AppBundle\Entity\AdherentActivationToken;
use AppBundle\Security\Http\Session\AnonymousFollowerSession;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\AppBundle\Controller\ControllerTestTrait;
use Tests\AppBundle\MysqlWebTestCase;

abstract class AbstractGroupControllerTest extends MysqlWebTestCase
{
    use ControllerTestTrait;

    public function testAnonymousUserCanFollowGroupWhileLoginIn()
    {
        $groupUrl = $this->getGroupUrl();
        $crawler = $this->client->request('GET', $groupUrl);

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $connectLink = $crawler->filter('#committee-login-link');

        $this->assertCount(1, $connectLink);

        $this->client->click($connectLink->link());

        $this->assertClientIsRedirectedTo('/connexion', $this->client);

        $crawler = $this->client->followRedirect();

        $this->client->submit($crawler->selectButton('Connexion')->form([
            '_adherent_email' => 'benjyd@aol.com',
            '_adherent_password' => 'HipHipHip',
        ]));

        $this->assertClientIsRedirectedTo($groupUrl, $this->client);
    }

    public function testAnonymousUserCanFollowGroupWhileRegistering()
    {
        $groupUrl = $this->getGroupUrl();
        $crawler = $this->client->request('GET', $groupUrl);

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $registrationLink = $crawler->filter('#committee-register-link');

        $this->assertSame(
            sprintf('%s?%s=/inscription', $groupUrl, AnonymousFollowerSession::AUTHENTICATION_INTENTION),
            $registrationLink->attr('href')
        );

        $this->client->click($registrationLink->link());

        $this->assertClientIsRedirectedTo('/inscription', $this->client);

        $crawler = $this->client->followRedirect();

        $this->client->submit($crawler->selectButton('Créer mon compte')->form([
            'g-recaptcha-response' => 'dummy',
            'new_member_ship_request' => [
                'firstName' => 'Paul',
                'lastName' => 'Dupont',
                'emailAddress' => [
                    'first' => 'paul@dupont.tld',
                    'second' => 'paul@dupont.tld',
                ],
                'password' => '#example!12345#',
                'address' => ['postalCode' => '75008'],
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

        $this->assertClientIsRedirectedTo($groupUrl, $this->client);
    }

    abstract protected function getGroupUrl(): string;
}
