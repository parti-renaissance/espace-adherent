<?php

namespace Tests\App\Controller\EnMarche\EventManager;

use App\DataFixtures\ORM\LoadCoalitionData;
use App\Mailer\Message\EventRegistrationConfirmationMessage;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Tests\App\Controller\ControllerTestTrait;

class CoalitionEventManagerControllerTest extends WebTestCase
{
    use ControllerTestTrait;

    public function testListCoalitionEvents(): void
    {
        $this->authenticateAsAdherent($this->client, 'jacques.picard@en-marche.fr');

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-coalition/evenements');

        $this->assertCount(13, $crawler->filter('tbody tr.event__item'));
    }

    public function testListMyCoalitionEvents(): void
    {
        $this->authenticateAsAdherent($this->client, 'jacques.picard@en-marche.fr');

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-coalition/mes-evenements');

        $this->assertCount(4, $crawler->filter('tbody tr.event__item'));
    }

    public function testCreateCoalitionEventFailed(): void
    {
        $this->authenticateAsAdherent($this->client, 'jacques.picard@en-marche.fr');

        $this->client->request(Request::METHOD_GET, '/espace-coalition/evenements/creer');

        $data = [];

        $this->client->submit($this->client->getCrawler()->selectButton('Enregistrer')->form(), $data);
        $this->assertSame(7, $this->client->getCrawler()->filter('.form__errors')->count());

        $this->assertSame('Cette valeur ne doit pas être vide.',
            $this->client->getCrawler()->filter('#event-name-field > .form__errors > li')->text());
        $this->assertSame('Cette valeur ne doit pas être nulle.',
            $this->client->getCrawler()->filter('#event-category-field > .form__errors > li')->text());
        $this->assertSame('Cette valeur ne doit pas être vide.',
            $this->client->getCrawler()->filter('#event-coalition-field > .form__errors > li')->text());
        $this->assertSame('Cette valeur ne doit pas être vide.',
            $this->client->getCrawler()->filter('#event-description-field > .form__errors > li')->text());
        $this->assertSame('L\'adresse est obligatoire.',
            $this->client->getCrawler()->filter('#event-address-address-field > .form__errors > li')->text());
        $this->assertSame('Votre adresse n\'est pas reconnue. Vérifiez qu\'elle soit correcte.',
            $this->client->getCrawler()->filter('#event-address > .form__errors > li')->text());
        $this->assertSame('Veuillez sélectionner un pays.',
            $this->client->getCrawler()->filter('#event-address-country-field > .form__errors > li')->text());
    }

    public function testCreateEventSuccessful(): void
    {
        $this->authenticateAsAdherent($this->client, 'jacques.picard@en-marche.fr');

        $this->client->request(Request::METHOD_GET, '/espace-coalition/evenements/creer');

        $data = [];
        $data['event_command']['name'] = 'Nouveau événement des coalitions';
        $data['event_command']['category'] = $this->getEventCategoryIdForName('Événement innovant');
        $data['event_command']['coalition'] = $this->getCoalitionRepository()->findOneBy(['uuid' => LoadCoalitionData::COALITION_1_UUID])->getId();
        $data['event_command']['beginAt'] = '2023-06-14 16:15';
        $data['event_command']['finishAt'] = '2023-06-15 23:00';
        $data['event_command']['address']['address'] = '92 boulevard victor hugo';
        $data['event_command']['address']['cityName'] = 'clichy';
        $data['event_command']['address']['postalCode'] = '92110';
        $data['event_command']['address']['country'] = 'FR';
        $data['event_command']['description'] = 'Nouveau événement';
        $data['event_command']['timeZone'] = 'Europe/Paris';

        $this->client->submit($this->client->getCrawler()->selectButton('Enregistrer')->form(), $data);

        $this->assertSame('L\'événement « Nouveau événement des coalitions » a bien été créé.', $this->client->getCrawler()->filter('.box-success h2')->text());

        $this->assertCountMails(0, EventRegistrationConfirmationMessage::class, 'jacques.picard@en-marche.fr');
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->init();
    }
}
