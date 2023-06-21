<?php

namespace Tests\App\Controller\EnMarche\Jecoute;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\App\AbstractEnMarcheWebTestCase;
use Tests\App\Controller\ControllerTestTrait;

#[Group('functional')]
class JecouteRegionControllerTest extends AbstractEnMarcheWebTestCase
{
    use ControllerTestTrait;

    #[DataProvider('provideAdherentsWithNoAccess')]
    public function testCannotEditJecouteRegion(string $adherentEmail)
    {
        $this->authenticateAsAdherent($this->client, $adherentEmail);

        $this->client->request(Request::METHOD_GET, '/espace-candidat/campagne/editer');

        self::assertResponseStatusCode(Response::HTTP_FORBIDDEN, $this->client->getResponse());
    }

    #[DataProvider('provideAdherentsWithAccess')]
    public function testEditJecouteRegion(string $adherentEmail)
    {
        $this->authenticateAsAdherent($this->client, $adherentEmail);

        $crawler = $this->client->request('GET', '/espace-candidat/utilisateurs');
        $this->isSuccessful($this->client->getResponse());

        self::assertCount(1, $link = $crawler->filter('nav.manager-sidebar__menu ul li a:contains("Personnaliser")'));

        $crawler = $this->client->click($link->link());

        self::assertSame('/espace-candidat/campagne/editer', $this->client->getRequest()->getPathInfo());
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        self::assertSame('Créer une personnalisation', $crawler->filter('.jecoute-region h3')->text());

        $csrfInput = $crawler->filter('form input[id$=__token]')->first();
        $formName = str_replace('__token', '', $csrfInput->attr('id'));

        $form = $crawler->selectButton('Enregistrer')->form();

        $values = $form->getPhpValues()[$formName];
        $values['subtitle'] = 'Campagne en Ile-de-France';
        $values['description'] = 'Description de la campagne';
        $values['primaryColor'] = 'purple';
        $values['logoFile']['croppedImage'] = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAfQAAAH0CAYAAADL1t+';

        $this->client->request($form->getMethod(), $form->getUri(), [$formName => $values]);

        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());

        $crawler = $this->client->followRedirect();

        self::assertSame('La personnalisation a été créée avec succès.', $crawler->filter('.flash div.flash__inner')->text());
        self::assertSame('Modifier la personnalisation', $crawler->filter('.jecoute-region h3')->text());

        $csrfInput = $crawler->filter('form input[id$=__token]')->first();
        $formName = str_replace('__token', '', $csrfInput->attr('id'));

        $form = $crawler->selectButton('Enregistrer')->form();

        $values = $form->getPhpValues()[$formName];
        $values['subtitle'] = 'Campagne en Ile-de-France modifiée';
        $values['description'] = 'Description de la campagne modifiée';
        $values['primaryColor'] = 'green';

        $this->client->request($form->getMethod(), $form->getUri(), [$formName => $values]);

        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());

        $crawler = $this->client->followRedirect();

        self::assertSame('La personnalisation a été modifiée avec succès.', $crawler->filter('.flash div.flash__inner')->text());
    }

    public static function provideAdherentsWithNoAccess(): iterable
    {
        yield ['benjyd@aol.com'];
        yield ['michelle.dufour@example.ch'];
        yield ['luciole1989@spambox.fr'];   // has a department as candidate managed area
        yield ['francis.brioul@yahoo.com']; // has a canton as candidate managed area
    }

    public static function provideAdherentsWithAccess(): iterable
    {
        yield ['jacques.picard@en-marche.fr', 'Espace candidat'];  // has a region as candidate managed area
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->disableRepublicanSilence();
    }
}
