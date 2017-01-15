<?php

namespace Tests\AppBundle\Controller\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\AppBundle\Controller\AbstractControllerTest;

class AdherentSecurityControllerTest extends AbstractControllerTest
{
    public function testLoginAction()
    {
        $client = static::createClient();

        $crawler = $client->request(Request::METHOD_GET, '/espace-adherent/connexion');

        $this->assertResponseStatusCode(Response::HTTP_OK, $client->getResponse());
        $this->assertCount(1, $crawler->filter('form[name="app_login"]'));
        $this->assertCount(0, $crawler->filter('.login__error'));
    }

    public function testLoginCheckFailsOnInvalidCredentials()
    {
        $client = static::createClient();

        $client->request(Request::METHOD_POST, '/espace-adherent/connexion/check', []);

        $this->assertResponseStatusCode(Response::HTTP_FOUND, $client->getResponse());
        $this->assertClientIsRedirectedTo('/espace-adherent/connexion', $client);

        $crawler = $client->followRedirect();

        $this->assertResponseStatusCode(Response::HTTP_OK, $client->getResponse());
        $this->assertCount(1, $error = $crawler->filter('.login__error'));
        $this->assertSame('Identifiants invalides.', trim($error->text()));
    }
}
