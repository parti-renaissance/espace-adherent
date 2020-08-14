<?php

namespace Tests\App\Controller\EnMarche\TerritorialCouncil;

use App\DataFixtures\ORM\LoadAdherentData;
use App\DataFixtures\ORM\LoadTerritorialCouncilMembershipData;
use App\Entity\VotingPlatform\Designation\Designation;
use App\Mailer\Message\TerritorialCouncilCandidacyInvitationAcceptedMessage;
use App\Mailer\Message\TerritorialCouncilCandidacyInvitationCreatedMessage;
use App\Mailer\Message\TerritorialCouncilCandidacyInvitationDeclinedMessage;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Tests\App\Controller\ControllerTestTrait;
use Tests\App\Mandrill\MailAssertTrait;

/**
 * @group functional
 * @group designation
 */
class CandidatureControllerTest extends WebTestCase
{
    use ControllerTestTrait;
    use MailAssertTrait;

    public function testICannotCandidateIfTerritorialCouncilHasNotAttachedDesignation(): void
    {
        $this->authenticateAsAdherent($this->client, 'carl999@example.fr');

        $this->client->request('GET', '/conseil-territorial');
        $this->assertResponseStatusCode(200, $response = $this->client->getResponse());
        $this->assertContains('Conseil territorial du département 92', $response->getContent());
        $this->assertNotContains('Désignation des binômes paritaires siégeant au Comité politique', $response->getContent());

        $this->client->request('GET', '/conseil-territorial/candidature');
        $this->assertResponseStatusCode(Response::HTTP_FORBIDDEN, $this->client->getResponse());
    }

    public function testICannotModifyIfItIsNotCandidatePeriod(): void
    {
        $adherent = $this->getAdherent(LoadAdherentData::ADHERENT_5_UUID);

        $this->authenticateAsAdherent($this->client, $adherent->getEmailAddress());

        $crawler = $this->client->request('GET', '/conseil-territorial');
        $this->assertResponseStatusCode(200, $response = $this->client->getResponse());
        $this->assertContains('Conseil territorial de Paris', $crawler->filter('.territorial-council__infos li')->first()->text());

        /** @var Designation $designation */
        $designation = $adherent->getTerritorialCouncilMembership()->getTerritorialCouncil()->getCurrentDesignation();
        $designation->setCandidacyEndDate(new \DateTime('-2 hours'));
        $this->getEntityManager(Designation::class)->flush();

        $this->client->request('GET', '/conseil-territorial');
        $this->assertNotContains('Modifier mes informations', $this->client->getResponse()->getContent());

        $this->client->request('GET', '/conseil-territorial/candidature');
        $this->assertClientIsRedirectedTo('/conseil-territorial', $this->client);
        $this->client->followRedirect();

        $this->assertContains('Vous ne pouvez pas candidater pour cette désignation.', $content = $this->client->getResponse()->getContent());
        $this->assertNotContains('Retirer ma pré-candidature', $content);

        $this->client->request('GET', '/conseil-territorial/candidature/retirer');

        $this->assertClientIsRedirectedTo('/conseil-territorial', $this->client);
        $this->client->followRedirect();

        $this->assertContains('Vous ne pouvez pas retirer votre candidature.', $this->client->getResponse()->getContent());
    }

    public function testICanRemoveMyCandidatureSuccessfully(): void
    {
        $adherent = $this->getAdherent(LoadAdherentData::ADHERENT_5_UUID);

        $this->authenticateAsAdherent($this->client, $adherent->getEmailAddress());

        $crawler = $this->client->request('GET', '/conseil-territorial');
        $this->client->click($crawler->selectLink('Retirer ma pré-candidature')->link());

        $crawler = $this->client->followRedirect();

        $this->assertContains('Votre candidature a bien été supprimée', $this->client->getResponse()->getContent());
        $this->assertSame('Je candidate en binôme', $crawler->filter('.instance__elections-box a.btn--pink')->text());
    }

    public function testICanUpdateMyCandidatureSuccessfully(): void
    {
        $adherent = $this->getAdherent(LoadAdherentData::ADHERENT_5_UUID);

        $this->authenticateAsAdherent($this->client, $adherent->getEmailAddress());

        $crawler = $this->client->request('GET', '/conseil-territorial');
        $crawler = $this->client->click($crawler->selectLink('Modifier mes informations')->link());

        $this->assertStringEndsWith('/conseil-territorial/candidature', $crawler->getUri());

        $form = $crawler->selectButton('Enregistrer')->form();

        $values = $form->getValues();

        $this->assertArraySubset([
            'territorial_council_candidacy[biography]' => 'Voluptas ea rerum eligendi rerum ipsum optio iusto qui. Harum minima labore tempore odio doloribus sint nihil veniam. Sint voluptas et ea cum ipsa aut. Odio ut sequi at optio mollitia asperiores voluptas.',
            'territorial_council_candidacy[faithStatement]' => 'Eum earum explicabo assumenda nesciunt hic ea. Veniam magni assumenda ab fugiat dolores consequatur voluptatem. Recusandae explicabo quia voluptatem magnam.',
        ], $values);

        $this->client->submit($form, [
            'territorial_council_candidacy[biography]' => 'Bonjour, voici ma bio',
            'territorial_council_candidacy[faithStatement]' => 'ma plus belle profession de foi ...',
        ]);

        $this->client->followRedirect();

        $this->assertContains('Votre candidature a bien été enregistrée', $this->client->getResponse()->getContent());
    }

    public function testICanCreateMyCandidatureAndInviteAnotherMembership(): void
    {
        $adherent = $this->getAdherent(LoadAdherentData::ADHERENT_5_UUID);

        $this->authenticateAsAdherent($this->client, $adherent->getEmailAddress());

        $crawler = $this->client->request('GET', '/conseil-territorial');
        $crawler = $this->client->click($crawler->filter('.territorial-council__aside--section')->selectLink('Gérer')->link());

        $this->assertSame('Pierre Kiroule', $crawler->filter('.l__row.identity .font-roboto')->text());

        $this->assertContains('Modifier ma demande de binôme', $content = $this->client->getResponse()->getContent());
        $this->assertContains('Modifier mes informations', $content);

        $this->client->click($crawler->selectLink('Retirer ma pré-candidature')->link());
        $crawler = $this->client->followRedirect();
        $crawler = $this->client->click($crawler->selectLink('Je candidate en binôme')->link());

        $form = $crawler->selectButton('Enregistrer et choisir mon binôme')->form();

        $crawler = $this->client->submit($form);

        self::assertCount(4, $errors = $crawler->filter('.form__error'));

        self::assertSame('Photo est obligatoire', $errors->eq(0)->text());
        self::assertSame('Cette valeur ne doit pas être vide.', $errors->eq(1)->text());
        self::assertSame('Cette valeur ne doit pas être vide.', $errors->eq(2)->text());
        self::assertSame('Vous devez cocher la case pour continuer', $errors->eq(3)->text());

        $this->client->submit($form, [
            'territorial_council_candidacy[croppedImage]' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAfQAAAH0CAYAAADL1t+',
            'territorial_council_candidacy[biography]' => 'ma bio',
            'territorial_council_candidacy[faithStatement]' => 'ma profession de foi',
            'territorial_council_candidacy[accept]' => true,
        ]);

        $this->assertClientIsRedirectedTo('/conseil-territorial/candidature/choix-de-binome', $this->client);

        // Choice of membership
        $crawler = $this->client->followRedirect();
        $form = $crawler->selectButton('Envoyer l\'invitation')->form();

        $crawler = $this->client->submit($form);

        self::assertCount(1, $errors = $crawler->filter('.form__error'));
        self::assertSame('Cette valeur ne doit pas être vide.', $errors->eq(0)->text());

        $crawler = $this->client->submit($form, [
            'candidacy_quality[quality]' => 'department_councilor',
            'candidacy_quality[invitation][membership]' => LoadTerritorialCouncilMembershipData::MEMBERSHIP_UUID1,
        ]);

        self::assertCount(1, $errors = $crawler->filter('.form__error'));
        self::assertSame('La qualité choisie n\'est pas compatible avec votre choix de binôme', $errors->eq(0)->text());

        $this->client->submit($form, [
            'candidacy_quality[quality]' => 'city_councilor',
            'candidacy_quality[invitation][membership]' => LoadTerritorialCouncilMembershipData::MEMBERSHIP_UUID1,
        ]);

        $this->assertClientIsRedirectedTo('/conseil-territorial/candidature/choix-de-binome/fini', $this->client);

        $this->assertCountMails(1, TerritorialCouncilCandidacyInvitationCreatedMessage::class, 'kiroule.p@blabla.tld');
    }

    public function testICanDeclineAnInvitation(): void
    {
        $adherent = $this->getAdherent(LoadAdherentData::ADHERENT_12_UUID);

        $this->authenticateAsAdherent($this->client, $adherent->getEmailAddress());

        $crawler = $this->client->request('GET', '/conseil-territorial');
        $crawler = $this->client->click($crawler->filter('.territorial-council__aside--section')->selectLink('Gérer')->link());

        self::assertCount(1, $crawler->filter('.candidacy-invitation'));
        self::assertSame('Gisele Berthoux', trim($crawler->filter('.candidacy-invitation .l__row .l__row')->text()));

        $this->client->click($crawler->selectLink('Décliner')->link());
        $this->client->followRedirect();

        self::assertContains('Invitation a bien été déclinée', $this->client->getResponse()->getContent());

        $this->assertCountMails(1, TerritorialCouncilCandidacyInvitationDeclinedMessage::class, 'gisele-berthoux@caramail.com');
    }

    public function testICanAcceptAnInvitation(): void
    {
        $adherent = $this->getAdherent(LoadAdherentData::ADHERENT_12_UUID);

        $this->authenticateAsAdherent($this->client, $adherent->getEmailAddress());

        $crawler = $this->client->request('GET', '/conseil-territorial');
        $crawler = $this->client->click($crawler->filter('.territorial-council__aside--section')->selectLink('Gérer')->link());

        $crawler = $this->client->click($crawler->selectLink('Accepter')->link());

        $form = $crawler->selectButton('Accepter et enregistrer')->form();

        $values = $form->getValues();

        self::assertArraySubset([
            'territorial_council_candidacy[faithStatement]' => 'Eum earum explicabo assumenda nesciunt hic ea. Veniam magni assumenda ab fugiat dolores consequatur voluptatem. Recusandae explicabo quia voluptatem magnam.',
            'territorial_council_candidacy[isPublicFaithStatement]' => true,
        ], $values);

        $this->client->submit($form, [
            'territorial_council_candidacy[croppedImage]' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAfQAAAH0CAYAAADL1t+',
            'territorial_council_candidacy[biography]' => 'ma bio',
            'territorial_council_candidacy[faithStatement]' => 'ma profession de foi',
            'territorial_council_candidacy[isPublicFaithStatement]' => false,
            'territorial_council_candidacy[accept]' => true,
        ]);

        $this->assertClientIsRedirectedTo('/conseil-territorial', $this->client);

        $this->assertCountMails(1, TerritorialCouncilCandidacyInvitationAcceptedMessage::class, 'gisele-berthoux@caramail.com');
        $this->assertCountMails(1, TerritorialCouncilCandidacyInvitationAcceptedMessage::class, 'kiroule.p@blabla.tld');

        $crawler = $this->client->followRedirect();

        self::assertContains('Votre candidature a bien été enregistrée', $this->client->getResponse()->getContent());

        $crawler = $this->client->click($crawler->selectLink('Modifier mes informations')->link());
        $form = $crawler->selectButton('Enregistrer')->form();

        self::assertArraySubset([
            'territorial_council_candidacy[faithStatement]' => 'ma profession de foi',
        ], $values = $form->getValues());
        self::assertArrayNotHasKey('territorial_council_candidacy[isPublicFaithStatement]', $values);

        $adherent = $this->getAdherent(LoadAdherentData::ADHERENT_5_UUID);

        $this->authenticateAsAdherent($this->client, $adherent->getEmailAddress());

        $crawler = $this->client->request('GET', '/conseil-territorial');
        $crawler = $this->client->click($crawler->selectLink('Modifier mes informations')->link());
        $form = $crawler->selectButton('Enregistrer')->form();

        self::assertArraySubset([
            'territorial_council_candidacy[faithStatement]' => 'ma profession de foi',
        ], $values = $form->getValues());
        self::assertArrayNotHasKey('territorial_council_candidacy[isPublicFaithStatement]', $values);
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
