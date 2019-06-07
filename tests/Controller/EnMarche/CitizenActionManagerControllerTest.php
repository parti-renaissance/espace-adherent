<?php

namespace Tests\AppBundle\Controller\EnMarche;

use AppBundle\DataFixtures\ORM\LoadCitizenActionData;
use AppBundle\Entity\CitizenAction;
use AppBundle\Mailer\Message\CitizenActionCancellationMessage;
use AppBundle\Mailer\Message\CitizenActionContactParticipantsMessage;
use AppBundle\Mailer\Message\CitizenActionNotificationMessage;
use AppBundle\Mailer\Message\EventRegistrationConfirmationMessage;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Ramsey\Uuid\Uuid;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\AppBundle\Controller\ControllerTestTrait;

/**
 * @group functional
 * @group citizenAction
 */
class CitizenActionManagerControllerTest extends WebTestCase
{
    use ControllerTestTrait;

    public function testCreateCitizenActionIsForbiddenIfUserIsNotProjectOrganizer()
    {
        $this->authenticateAsAdherent($this->client, 'carl999@example.fr');
        $this->client->request(Request::METHOD_GET, '/projets-citoyens/75008-le-projet-citoyen-a-paris-8/actions/creer');

        static::assertResponseStatusCode(Response::HTTP_FORBIDDEN, $this->client->getResponse());

        $crawler = $this->client->request(Request::METHOD_GET, '/projets-citoyens/75008-le-projet-citoyen-a-paris-8');
        $this->assertSame(0, $crawler->selectLink('Créer une action citoyenne')->count());
    }

    public function testCreateCitizenActionIsForbiddenIfProjectIsNotApproved()
    {
        $this->authenticateAsAdherent($this->client, 'benjyd@aol.com');
        $this->client->request(Request::METHOD_GET, '/projets-citoyens/13003-le-projet-citoyen-a-marseille/actions/creer');

        static::assertResponseStatusCode(Response::HTTP_NOT_FOUND, $this->client->getResponse());
        $crawler = $this->client->request(Request::METHOD_GET, '/projets-citoyens/75008-le-projet-citoyen-a-paris-8');
        $this->assertSame(0, $crawler->selectLink('Créer une action citoyenne')->count());
    }

    public function testCreateCitizenActionFailed()
    {
        $this->authenticateAsAdherent($this->client, 'jacques.picard@en-marche.fr');
        $crawler = $this->client->request(Request::METHOD_GET, '/projets-citoyens/75008-le-projet-citoyen-a-paris-8');

        $crawler = $this->client->click($crawler->selectLink('Gérer le projet →')->link());
        $this->client->click($crawler->selectLink('+ Action citoyenne')->link());

        $this->assertSame('/projets-citoyens/75008-le-projet-citoyen-a-paris-8/actions/creer', $this->client->getRequest()->getPathInfo());

        $this->client->submit($this->client->getCrawler()->selectButton('Je crée mon action citoyenne')->form());

        $this->assertSame(5, $this->client->getCrawler()->filter('.form__errors')->count());
        $this->assertSame(
            'Cette valeur ne doit pas être vide.',
            $this->client->getCrawler()->filter('#citizen-action-name-field > .form__errors > li')->text()
        );
        $this->assertSame(
            'Cette valeur ne doit pas être vide.',
            $this->client->getCrawler()->filter('#citizen-action-description-field > .form__errors > li')->text()
        );
        $this->assertSame(
            'Votre adresse n\'est pas reconnue. Vérifiez qu\'elle soit correcte.',
            $this->client->getCrawler()->filter('#citizen-action-address > .form__errors > li')->text()
        );
        $this->assertSame(
            'L\'adresse est obligatoire.',
            $this->client->getCrawler()->filter('#citizen-action-address-address-field > .form__errors > li')->text()
        );

        $this->assertSame(
            'Veuillez sélectionner un pays.',
            $this->client->getCrawler()->filter('#citizen-action-address-country-field > .form__errors > li')->text()
        );

        $data = [];
        $data['citizen_action']['name'] = 'n';
        $data['citizen_action']['description'] = 'a';
        $this->client->submit($this->client->getCrawler()->selectButton('Je crée mon action citoyenne')->form(), $data);

        $this->assertSame(5, $this->client->getCrawler()->filter('.form__errors')->count());
        $this->assertSame(
            'Vous devez saisir au moins 5 caractères.',
            $this->client->getCrawler()->filter('#citizen-action-name-field > .form__errors > li')->text()
        );
        $this->assertSame(
            'Vous devez saisir au moins 10 caractères.',
            $this->client->getCrawler()->filter('#citizen-action-description-field > .form__errors > li')->text()
        );

        // Check that "Action citoyenne" is the only category choice and is pre selected
        $category = $this->client->getCrawler()->filter('#citizen_action_category > option');

        $this->assertCount(1, $category);
        $this->assertSame('Action citoyenne', $category->text());
        $this->assertSame('selected', $category->attr('selected'));
    }

    public function testCreateCitizenActionSuccessful()
    {
        $this->authenticateAsAdherent($this->client, 'jacques.picard@en-marche.fr');
        $crawler = $this->client->request(Request::METHOD_GET, '/projets-citoyens/75008-le-projet-citoyen-a-paris-8');

        $crawler = $this->client->click($crawler->selectLink('Gérer le projet →')->link());
        $this->client->click($crawler->selectLink('+ Action citoyenne')->link());

        $this->assertSame('/projets-citoyens/75008-le-projet-citoyen-a-paris-8/actions/creer', $this->client->getRequest()->getPathInfo());

        $crawler = $this->client->request(Request::METHOD_GET, '/projets-citoyens/75008-le-projet-citoyen-a-paris-8/actions/creer');

        $data['citizen_action']['name'] = 'mon Action Citoyenne';
        $data['citizen_action']['description'] = 'Ma première action citoyenne';
        $data['citizen_action']['address']['address'] = '44 rue des Courcelles';
        $data['citizen_action']['address']['postalCode'] = '75008';
        $data['citizen_action']['address']['cityName'] = 'Paris';
        $data['citizen_action']['address']['country'] = 'FR';

        $this->client->submit($crawler->selectButton('Je crée mon action citoyenne')->form(), $data);

        $this->assertSame(0, $this->client->getCrawler()->filter('.form__errors')->count());
        /** @var CitizenAction $citizenAction */
        $this->assertInstanceOf(CitizenAction::class, $citizenAction = $this->getCitizenActionRepository()->findOneBy(['slug' => (new \DateTime())->format('Y-m-d').'-mon-action-citoyenne']));
        $this->assertSame('Mon Action Citoyenne', $citizenAction->getName());
        $this->assertCountMails(0, EventRegistrationConfirmationMessage::class, 'jacques.picard@en-marche.fr');
        $this->assertCountMails(1, CitizenActionNotificationMessage::class, 'jacques.picard@en-marche.fr');
        $this->assertCountMails(1, CitizenActionNotificationMessage::class, 'gisele-berthoux@caramail.com');
        $this->assertCountMails(1, CitizenActionNotificationMessage::class, 'luciole1989@spambox.fr');
        $this->assertCountMails(0, CitizenActionNotificationMessage::class, 'benoit-da-m@stah.fr');
    }

    public function testOrganizerCanCancelCitizenAction()
    {
        $this->authenticateAsAdherent($this->client, 'jacques.picard@en-marche.fr');

        $uuid = LoadCitizenActionData::CITIZEN_ACTION_4_UUID;
        /** @var CitizenAction $citizenAction */
        $citizenAction = $this->getCitizenActionRepository()->findOneBy(['uuid' => $uuid]);
        $crawler = $this->client->request(Request::METHOD_GET, sprintf('/action-citoyenne/%s', $citizenAction->getSlug()));

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $crawler = $this->client->click($crawler->selectLink('Annuler cette action citoyenne')->link());

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertEquals(sprintf('http://%s/projets-citoyens/%s/actions/%s/annuler', $this->hosts['app'], $citizenAction->getCitizenProject()->getSlug(), $citizenAction->getSlug()), $this->client->getRequest()->getUri());

        $this->client->submit($crawler->selectButton('Oui, annuler l\'action citoyenne')->form());

        $this->assertStatusCode(Response::HTTP_FOUND, $this->client);

        // Follow the redirect and check the adherent can see the citizen project page
        $crawler = $this->client->followRedirect();

        $this->assertStatusCode(Response::HTTP_OK, $this->client);
        $this->seeFlashMessage($crawler, 'L\'action citoyenne a bien été annulée.');

        $messages = $this->getEmailRepository()->findMessages(CitizenActionCancellationMessage::class);
        /** @var CitizenActionCancellationMessage $message */
        $message = array_shift($messages);

        // Two mails have been sent
        $this->assertCount(5, $message->getRecipients());
    }

    public function testOrganizerCanSeeParticipants()
    {
        $this->authenticateAsAdherent($this->client, 'jacques.picard@en-marche.fr');
        /** @var CitizenAction $citizenAction */
        $citizenAction = $this->getCitizenActionRepository()->findOneBy(['uuid' => LoadCitizenActionData::CITIZEN_ACTION_4_UUID]);
        $crawler = $this->client->request(Request::METHOD_GET, sprintf('/action-citoyenne/%s', $citizenAction->getSlug()));
        $crawler = $this->client->click($crawler->selectLink('5 inscrits')->link());

        $this->assertTrue($this->seeParticipantsList($crawler, 5), 'There should be 5 participants in the list.');
    }

    public function testOrganizerCanExportParticipantsWithWrongUuids()
    {
        $this->authenticateAsAdherent($this->client, 'jacques.picard@en-marche.fr');

        /** @var CitizenAction $citizenAction */
        $citizenAction = $this->getCitizenActionRepository()->findOneBy(['uuid' => LoadCitizenActionData::CITIZEN_ACTION_4_UUID]);
        $exportUrl = sprintf('/projets-citoyens/75008-le-projet-citoyen-a-paris-8/actions/%s/participants/exporter', $citizenAction->getSlug());

        $crawler = $this->client->request(Request::METHOD_GET, sprintf('/action-citoyenne/%s/participants', $citizenAction->getSlug()));

        $this->assertStatusCode(Response::HTTP_OK, $this->client);

        $this->client->request(Request::METHOD_POST, $exportUrl, [
            'token' => $crawler->filter('#members-export-token')->attr('value'),
            'exports' => json_encode(['wrong_uuid']),
        ]);

        $this->assertStatusCode(Response::HTTP_BAD_REQUEST, $this->client);
    }

    public function testOrganizerCanExportParticipants()
    {
        $this->authenticateAsAdherent($this->client, 'jacques.picard@en-marche.fr');

        /** @var CitizenAction $citizenAction */
        $citizenAction = $this->getCitizenActionRepository()->findOneBy(['uuid' => LoadCitizenActionData::CITIZEN_ACTION_4_UUID]);
        $crawler = $this->client->request(Request::METHOD_GET, sprintf('/action-citoyenne/%s/participants', $citizenAction->getSlug()));
        $token = $crawler->filter('#members-export-token')->attr('value');
        $uuids = (array) $crawler->filter('input[name="members[]"]')->attr('value');
        $exportUrl = sprintf('/projets-citoyens/75008-le-projet-citoyen-a-paris-8/actions/%s/participants/exporter', $citizenAction->getSlug());

        $this->client->request(Request::METHOD_POST, $exportUrl, [
            'token' => $token,
            'exports' => json_encode($uuids),
        ]);

        $this->isSuccessful($this->client->getResponse());
        $this->assertCount(3, explode("\n", $this->client->getResponse()->getContent()));

        // Try to illegally export an adherent data
        $uuids[] = Uuid::uuid4();

        $this->client->request(Request::METHOD_POST, $exportUrl, [
            'token' => $token,
            'exports' => json_encode($uuids),
        ]);

        $this->isSuccessful($this->client->getResponse());
        $this->assertCount(3, explode("\n", $this->client->getResponse()->getContent()));

        $this->client->request(Request::METHOD_POST, $exportUrl, [
            'token' => $token,
            'exports' => json_encode([]),
        ]);

        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());
        $this->assertClientIsRedirectedTo(sprintf('/action-citoyenne/%s/participants', $citizenAction->getSlug()), $this->client);
    }

    public function testOrganizerCannotPrintParticipantsWithWrongUuids()
    {
        $this->authenticateAsAdherent($this->client, 'jacques.picard@en-marche.fr');

        /** @var CitizenAction $citizenAction */
        $citizenAction = $this->getCitizenActionRepository()->findOneBy(['uuid' => LoadCitizenActionData::CITIZEN_ACTION_4_UUID]);
        $crawler = $this->client->request(Request::METHOD_GET, sprintf('/action-citoyenne/%s/participants', $citizenAction->getSlug()));

        $printUrl = sprintf('/projets-citoyens/75008-le-projet-citoyen-a-paris-8/actions/%s/participants/imprimer', $citizenAction->getSlug());

        $this->client->request(Request::METHOD_POST, $printUrl, [
            'token' => $crawler->filter('#members-print-token')->attr('value'),
            'prints' => json_encode(['wrong_uuid']),
        ]);

        $this->assertStatusCode(Response::HTTP_BAD_REQUEST, $this->client);
    }

    public function testOrganizerCanPrintParticipants()
    {
        $this->authenticateAsAdherent($this->client, 'jacques.picard@en-marche.fr');

        /** @var CitizenAction $citizenAction */
        $citizenAction = $this->getCitizenActionRepository()->findOneBy(['uuid' => LoadCitizenActionData::CITIZEN_ACTION_4_UUID]);
        $crawler = $this->client->request(Request::METHOD_GET, sprintf('/action-citoyenne/%s/participants', $citizenAction->getSlug()));
        $printUrl = sprintf('/projets-citoyens/75008-le-projet-citoyen-a-paris-8/actions/%s/participants/imprimer', $citizenAction->getSlug());
        $token = $crawler->filter('#members-print-token')->attr('value');
        $uuids = (array) $crawler->filter('input[name="members[]"]')->attr('value');

        $this->client->request(Request::METHOD_POST, $printUrl, [
            'token' => $token,
            'prints' => json_encode($uuids),
        ]);

        $this->isSuccessful($this->client->getResponse());
        $this->assertTrue(
            $this->client->getResponse()->headers->contains(
                'Content-Type',
                'application/pdf'
            )
        );
    }

    public function testOrganizerCanContactParticipants()
    {
        $this->authenticateAsAdherent($this->client, 'jacques.picard@en-marche.fr');

        /** @var CitizenAction $citizenAction */
        $citizenAction = $this->getCitizenActionRepository()->findOneBy(['uuid' => LoadCitizenActionData::CITIZEN_ACTION_4_UUID]);
        $participantsUrl = sprintf('/action-citoyenne/%s/participants', $citizenAction->getSlug());
        $contactUrl = sprintf('/projets-citoyens/75008-le-projet-citoyen-a-paris-8/actions/%s/participants/contacter', $citizenAction->getSlug());
        $crawler = $this->client->request(Request::METHOD_GET, $participantsUrl);
        $token = $crawler->filter('#members-contact-token')->attr('value');
        $uuids = (array) $crawler->filter('input[name="members[]"]')->attr('value');

        $crawler = $this->client->request(Request::METHOD_POST, $contactUrl, [
            'token' => $token,
            'contacts' => json_encode($uuids),
        ]);

        $this->isSuccessful($this->client->getResponse());

        // Try to post with an empty subject and an empty message
        $crawler = $this->client->request(Request::METHOD_POST, $contactUrl, [
            'token' => $crawler->filter('input[name="token"]')->attr('value'),
            'contacts' => $crawler->filter('input[name="contacts"]')->attr('value'),
            'subject' => ' ',
            'message' => ' ',
        ]);

        $this->isSuccessful($this->client->getResponse());

        $this->assertSame('Cette valeur ne doit pas être vide.',
            $crawler->filter('.subject .form__errors > .form__error')->text()
        );

        $this->assertSame('Cette valeur ne doit pas être vide.',
            $crawler->filter('.message .form__errors > .form__error')->text()
        );

        $this->client->request(Request::METHOD_POST, $contactUrl, [
            'token' => $crawler->filter('input[name="token"]')->attr('value'),
            'contacts' => $crawler->filter('input[name="contacts"]')->attr('value'),
            'subject' => 'First contact',
            'message' => 'Hello world!',
        ]);

        $this->assertClientIsRedirectedTo($participantsUrl, $this->client);

        $crawler = $this->client->followRedirect();

        $this->seeFlashMessage($crawler, 'Félicitations, votre message a bien été envoyé aux inscrits sélectionnés.');

        // Email should have been sent
        $this->assertCount(1, $this->getEmailRepository()->findMessages(CitizenActionContactParticipantsMessage::class));

        // Try to illegally contact an adherent
        $uuids[] = Uuid::uuid4();

        $crawler = $this->client->request(Request::METHOD_POST, $contactUrl, [
            'token' => $token,
            'contacts' => json_encode($uuids),
        ]);

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertCount(1, json_decode($crawler->filter('input[name="contacts"]')->attr('value'), true));

        // Force the contact form with foreign uuid
        $this->client->request(Request::METHOD_POST, $contactUrl, [
            'token' => $crawler->filter('input[name="token"]')->attr('value'),
            'contacts' => json_encode($uuids),
            'subject' => 'First contact',
            'message' => 'Hello world!',
        ]);

        $this->assertClientIsRedirectedTo($participantsUrl, $this->client);
    }

    private function seeParticipantsList(Crawler $crawler, int $count): bool
    {
        return $count === \count($crawler->filter('table tbody tr'));
    }

    protected function setUp()
    {
        parent::setUp();

        $this->init();
    }

    protected function tearDown()
    {
        $this->kill();

        parent::tearDown();
    }
}
