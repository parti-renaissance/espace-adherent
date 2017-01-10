<?php

namespace Tests\AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AdherentControllerTest extends AbstractControllerTest
{
    public function testIndexActionIsSecured()
    {
        $client = static::createClient();

        $client->request(Request::METHOD_GET, '/espace-adherent/mon-profil');

        $this->assertResponseStatusCode(Response::HTTP_FOUND, $client->getResponse());
        $this->assertClientIsRedirectedTo('/espace-adherent/connexion', $client);
    }
}
