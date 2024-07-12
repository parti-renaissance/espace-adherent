<?php

namespace Tests\App\Controller\EnMarche;

use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\App\AbstractEnMarcheWebTestCase;
use Tests\App\Controller\ControllerTestTrait;

#[Group('functional')]
class FormationControllerTest extends AbstractEnMarcheWebTestCase
{
    use ControllerTestTrait;

    public function testFormationHomepageIsProtected(): void
    {
        $this->client->request(Request::METHOD_GET, '/espace-formation');

        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());
        $this->assertClientIsRedirectedTo('/connexion', $this->client);
    }

    public function testFormationFaqIsProtected(): void
    {
        $this->client->request(Request::METHOD_GET, '/espace-formation/faq');

        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());
        $this->assertClientIsRedirectedTo('/connexion', $this->client);
    }

    public function testAuthenticatedUserSeeHisFormation(): void
    {
        $this->authenticateAsAdherent($this->client, 'lolodie.dutemps@hotnix.tld');
        $this->client->request(Request::METHOD_GET, '/espace-formation');

        $this->assertStringContainsString('Première visite ?', $content = $this->client->getResponse()->getContent());
        $this->assertStringContainsString('Premier article du premier axe', $content);
        $this->assertStringContainsString('Deuxième article du premier axe', $content);
        $this->assertStringContainsString('Premier article du deuxième axe', $content);
        $this->assertStringContainsString('Deuxième article du deuxième axe', $content);
    }
}
