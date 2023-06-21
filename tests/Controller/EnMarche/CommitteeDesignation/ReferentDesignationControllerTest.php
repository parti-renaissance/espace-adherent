<?php

namespace Tests\App\Controller\EnMarche\CommitteeDesignation;

use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\HttpFoundation\Request;
use Tests\App\AbstractEnMarcheWebTestCase;
use Tests\App\Controller\ControllerTestTrait;

#[Group('functional')]
class ReferentDesignationControllerTest extends AbstractEnMarcheWebTestCase
{
    use ControllerTestTrait;

    public function testAsReferentICanSeeAllAvailableForPartialElectionCommittees(): void
    {
        $this->authenticateAsAdherent($this->client, 'referent@en-marche-dev.fr');

        $this->client->request(Request::METHOD_GET, '/espace-referent/comites');

        $crawler = $this->client->clickLink('+ Créer une partielle');
        $this->assertResponseIsSuccessful();

        self::assertCount(2, $tableRows = $crawler->filter('table.datagrid__table-manager tbody tr'));

        self::assertStringContainsString('Antenne En Marche de Fontainebleau', $tableRows->eq(0)->text());
        self::assertStringContainsString('Désigner le binôme d\'adhérents', $tableRows->eq(0)->text());
        self::assertStringContainsString('Renouveler l\'animatrice locale', $tableRows->eq(0)->text());

        self::assertStringContainsString('En Marche Dammarie-les-Lys', $tableRows->eq(1)->text());
        self::assertStringContainsString('Renouveler l\'animatrice locale', $tableRows->eq(1)->text());

        self::assertStringNotContainsStringIgnoringCase('Homme', $tableRows->text());
    }

    public function testAsReferentICannotCreatePartialElectionForCommitteeOutsideMyZone(): void
    {
        $this->authenticateAsAdherent($this->client, 'referent@en-marche-dev.fr');

        $this->client->request(Request::METHOD_GET, '/espace-referent/comites/en-marche-allemagne-2/designations/creer-une-partielle?type=committee_supervisor&pool=female');

        $this->assertResponseStatusCode(403, $this->client->getResponse());
    }

    public function testAsReferentICanCreatePartialElectionOnCommittee(): void
    {
        $this->authenticateAsAdherent($this->client, 'referent@en-marche-dev.fr');

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-referent/comites/designations/partielles');

        $crawler = $this->client->click($crawler->selectLink('Renouveler l\'animatrice locale')->eq(1)->link());

        self::assertStringContainsString('Renouvellement de l\'animatrice locale', $crawler->filter('form.em-form h2')->text());

        $crawler = $this->client->submit($crawler->selectButton('Suivant →')->form());

        self::assertCount(3, $crawler->filter('ul.form__errors'));

        $crawler = $this->client->submit($crawler->selectButton('Suivant →')->form(), [
            'partial_designation[voteStartDate]' => (new \DateTime('+7 days'))->format('Y-m-d H:i'),
            'partial_designation[voteEndDate]' => (new \DateTime('+14 days'))->format('Y-m-d H:i'),
            'partial_designation[message]' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec maximus convallis dolor, id ultricies lorem lobortis et. Vivamus bibendum leo et ullamcorper dapibus.',
        ]);

        $this->client->submit($crawler->selectButton('Confirmer')->form());

        $crawler = $this->client->followRedirect();

        self::assertStringContainsString('L\'élection partielle a bien été créée.', $crawler->filter('.flash__inner')->text());

        $crawler = $this->client->request('GET', '/comites/en-marche-dammarie-les-lys');

        self::assertStringNotContainsStringIgnoringCase('Élection du binôme paritaire d’Animateurs locaux', $crawler->filter('main.committee')->text());

        $token = $crawler->selectButton('Suivre ce comité')->attr('data-csrf-token');
        $this->client->request('POST', '/comites/en-marche-dammarie-les-lys/rejoindre', ['token' => $token], [], ['HTTP_X-Requested-With' => 'XMLHttpRequest']);

        $crawler = $this->client->request('GET', '/comites/en-marche-dammarie-les-lys');
        self::assertStringContainsString('Élection de l’Animatrice locale du comité', $crawler->filter('main.committee')->text());
    }
}
