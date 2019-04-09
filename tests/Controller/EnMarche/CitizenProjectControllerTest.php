<?php

namespace Tests\AppBundle\Controller\EnMarche;

use AppBundle\AdherentMessage\Command\CreateStaticSegmentCommand;
use AppBundle\DataFixtures\ORM\LoadAdherentData;
use AppBundle\DataFixtures\ORM\LoadCitizenProjectData;
use AppBundle\Entity\CitizenProject;
use AppBundle\Mailchimp\Synchronisation\Command\AddAdherentToStaticSegmentCommand;
use AppBundle\Mailchimp\Synchronisation\Command\RemoveAdherentFromStaticSegmentCommand;
use AppBundle\Mailer\Message\CitizenProjectNewFollowerMessage;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\AppBundle\MessengerTestTrait;

/**
 * @group functional
 * @group citizenProject
 */
class CitizenProjectControllerTest extends AbstractGroupControllerTest
{
    use MessengerTestTrait;

    public function testAnonymousUserCanSeeAnApprovedCitizenProject(): void
    {
        $this->client->request(Request::METHOD_GET, '/projets-citoyens/75008-le-projet-citoyen-a-paris-8');

        $this->assertStatusCode(Response::HTTP_OK, $this->client);
        $this->assertFalse($this->seeCommentSection());
        $this->assertFalse($this->seeReportLink());
        $this->assertSeeCitizenActions();
    }

    public function testAnonymousUserCannotSeeAPendingCitizenProject(): void
    {
        $this->client->request(Request::METHOD_GET, '/projets-citoyens/13003-le-projet-citoyen-a-marseille');

        $this->assertClientIsRedirectedTo('/connexion', $this->client);
    }

    public function testAdherentCannotSeeUnapprovedCitizenProject(): void
    {
        $this->authenticateAsAdherent($this->client, 'carl999@example.fr');
        $this->client->request(Request::METHOD_GET, '/projets-citoyens/13003-le-projet-citoyen-a-marseille');

        $this->assertResponseStatusCode(Response::HTTP_FORBIDDEN, $this->client->getResponse());
    }

    public function testAdherentCanSeeCitizenProject(): void
    {
        $this->authenticateAsAdherent($this->client, 'benjyd@aol.com');

        /** @var CitizenProject $citizenProject */
        $citizenProject = $this->getCitizenProjectRepository()->findOneByUuid(LoadCitizenProjectData::CITIZEN_PROJECT_1_UUID);
        $crawler = $this->client->request(Request::METHOD_GET, sprintf('/projets-citoyens/%s', $citizenProject->getSlug()));

        $this->isSuccessful($this->client->getResponse());
        $this->assertTrue($this->seeReportLink());
        $this->assertTrue($this->seeDefaultCitizenProjectImage());
        $this->assertFalse($this->seeCommentSection());

        $this->assertContains('Le problème', $crawler->filter('#citizen-project-problem-description > p:nth-child(1)')->text());
        $this->assertContains($citizenProject->getProblemDescription(), $crawler->filter('#citizen-project-problem-description > p:nth-child(2)')->text());
        $this->assertContains('Notre projet', $crawler->filter('#citizen-project-proposed-solution > p:nth-child(1)')->text());
        $this->assertContains($citizenProject->getProposedSolution(), $crawler->filter('#citizen-project-proposed-solution > p:nth-child(2)')->text());
        $this->assertContains('Les actions à lancer', $crawler->filter('#citizen-project-required-means > p:nth-child(1)')->text());
        $this->assertContains($citizenProject->getRequiredMeans(), $crawler->filter('#citizen-project-required-means > p:nth-child(2)')->text());
    }

    public function testCreatorCanSeePendingCitizenProject(): void
    {
        $this->authenticateAsAdherent($this->client, 'kiroule.p@blabla.tld');
        $crawler = $this->client->request(Request::METHOD_GET, '/projets-citoyens/10019-en-attente-projet-citoyen-a-new-york-city');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertFalse($this->seeCommentSection());
        $this->assertCount(0, $this->client->getCrawler()->selectLink('Signaler un abus'));
        $this->assertSame(
            "Votre projet citoyen est en attente de validation par les équipes d'En Marche !\n"
                .'Vous serez alerté(e) par e-mail quand il sera validé.',
            trim($crawler->filter('.citizen-project__waiting-for-approval')->text())
        );
    }

    public function testCreatorCanSeeRefusedCitizenProject(): void
    {
        $this->authenticateAsAdherent($this->client, 'jacques.picard@en-marche.fr');
        $crawler = $this->client->request(Request::METHOD_GET, '/projets-citoyens/75008-projet-citoyen-refuse-a-paris-8');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertFalse($this->seeCommentSection());
        $this->assertCount(0, $this->client->getCrawler()->selectLink('Signaler un abus'));
        $this->assertSame(
            "En l'état, votre projet citoyen n'a pas été validé par nos équipes.\n"
                ."Vous pouvez à tout moment modifier ce projet ou en proposer un nouveau.\n"
                ."Pour plus d'informations, reportez-vous à la Charte des Projets Citoyens.",
            trim($crawler->filter('.citizen-project__waiting-for-approval')->text())
        );
    }

    public function testAdministratorCanSeeACitizenProject(): void
    {
        $this->authenticateAsAdherent($this->client, 'jacques.picard@en-marche.fr');
        $this->client->request(Request::METHOD_GET, '/projets-citoyens/75008-le-projet-citoyen-a-paris-8');
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertCount(0, $this->client->getCrawler()->selectLink('Signaler un abus'));
    }

    public function testFollowerCanSeeACitizenProject(): void
    {
        $this->authenticateAsAdherent($this->client, 'carl999@example.fr');
        $this->client->request(Request::METHOD_GET, '/projets-citoyens/75008-le-projet-citoyen-a-paris-8');
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertTrue($this->seeReportLink());
    }

    public function testAjaxSearchCommittee()
    {
        $this->client->request(Request::METHOD_GET, '/projets-citoyens/comite/autocompletion?term=pa', [], [], [
            'HTTP_X-Requested-With' => 'XMLHttpRequest',
        ]);
        $this->assertStatusCode(Response::HTTP_FOUND, $this->client);
        $this->assertClientIsRedirectedTo('/connexion', $this->client);

        $this->authenticateAsAdherent($this->client, 'carl999@example.fr');
        $this->client->request(Request::METHOD_GET, '/projets-citoyens/comite/autocompletion?term=pa', [], [], [
            'HTTP_X-Requested-With' => 'XMLHttpRequest',
        ]);
        $this->assertStatusCode(Response::HTTP_OK, $this->client);

        $this->assertSame(\GuzzleHttp\json_encode([[
            'uuid' => LoadAdherentData::COMMITTEE_1_UUID,
            'name' => 'En Marche Paris 8',
        ]]), $this->client->getResponse()->getContent());

        // Search of a pending committee
        $this->client->request(Request::METHOD_GET, '/projets-citoyens/comite/autocompletion?term=marseille', [], [], [
            'HTTP_X-Requested-With' => 'XMLHttpRequest',
        ]);

        $this->assertStatusCode(Response::HTTP_OK, $this->client);
        $this->assertSame('[]', $this->client->getResponse()->getContent());

        $this->client->request(Request::METHOD_GET, '/projets-citoyens/comite/autocompletion', [], [], [
            'HTTP_X-Requested-With' => 'XMLHttpRequest',
        ]);
        $this->assertStatusCode(Response::HTTP_BAD_REQUEST, $this->client);
    }

    public function testCommitteeSupportCitizenProject()
    {
        $this->authenticateAsAdherent($this->client, 'jacques.picard@en-marche.fr');

        /** @var CitizenProject $citizenProject */
        $citizenProject = $this->getCitizenProjectRepository()->findOneByUuid(LoadCitizenProjectData::CITIZEN_PROJECT_2_UUID);

        $this->assertFalse($citizenProject->isApproved());
        $crawler = $this->client->request(Request::METHOD_GET, sprintf('/projets-citoyens/mon-comite-soutien/%s', $citizenProject->getSlug()));
        $this->client->submit($crawler->selectButton('Confirmer le soutien de notre comité pour ce projet')->form());
        $this->assertResponseStatusCode(Response::HTTP_FORBIDDEN, $this->client->getResponse());

        $citizenProject = $this->getCitizenProjectRepository()->findOneByUuid(LoadCitizenProjectData::CITIZEN_PROJECT_1_UUID);
        $committee = $this->getCommittee(LoadAdherentData::COMMITTEE_1_UUID);
        $this->assertCount(1, $citizenProject->getCommitteeSupports());

        $crawler = $this->client->request(Request::METHOD_GET, sprintf('/projets-citoyens/mon-comite-soutien/%s', $citizenProject->getSlug()));
        $this->client->submit($crawler->selectButton('Confirmer le soutien de notre comité pour ce projet')->form());
        $this->assertClientIsRedirectedTo(sprintf('/projets-citoyens/%s', $citizenProject->getSlug()), $this->client);
        $crawler = $this->client->followRedirect();
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->seeFlashMessage($crawler, sprintf('Votre comité %s soutient maintenant le projet citoyen %s',
            $committee->getName(),
            $citizenProject->getName()
        ));

        $this->manager->clear();
        $citizenProject = $this->getCitizenProjectRepository()->findOneByUuid(LoadCitizenProjectData::CITIZEN_PROJECT_1_UUID);
        $this->assertCount(1, $citizenProject->getApprovedCommitteeSupports());

        $crawler = $this->client->request(Request::METHOD_GET, sprintf('/projets-citoyens/mon-comite-soutien/%s', $citizenProject->getSlug()));

        $this->client->submit($crawler->selectButton('Confirmer le soutien de notre comité pour ce projet')->form());
        $this->assertClientIsRedirectedTo(sprintf('/projets-citoyens/%s', $citizenProject->getSlug()), $this->client);
        $crawler = $this->client->followRedirect();
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $this->seeFlashMessage($crawler, sprintf('Votre comité %s ne soutient plus le projet citoyen %s',
            $committee->getName(),
            $citizenProject->getName()
        ));

        $crawler = $this->client->request(Request::METHOD_GET, sprintf('/projets-citoyens/%s', $citizenProject->getSlug()));
        $committeeOnSupport = $crawler->filter('#support-committee')->filter('li');
        $this->assertSame(0, $committeeOnSupport->count());

        $citizenProject->removeCommitteeSupport($committee);
        $this->manager->flush();
        $this->manager->clear();

        $citizenProject = $this->getCitizenProjectRepository()->findOneByUuid(LoadCitizenProjectData::CITIZEN_PROJECT_1_UUID);
        $this->assertCount(1, $citizenProject->getCommitteeSupports()->toArray());

        $crawler = $this->client->request(Request::METHOD_GET, sprintf('/projets-citoyens/%s', $citizenProject->getSlug()));
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertSame('newbtn--small btn--blue b__nudge--top', $crawler->filter('button#committee-confirm-support')->attr('class'));

        $this->client->submit($crawler->selectButton('Soutenir ce projet avec mon comité')->form());
        $crawler = $this->client->followRedirect();
        $this->seeFlashMessage($crawler, sprintf('Votre comité %s soutient maintenant le projet citoyen %s',
            $committee->getName(),
            $citizenProject->getName()
        ));

        $this->manager->clear();
        $citizenProject = $this->getCitizenProjectRepository()->findOneByUuid(LoadCitizenProjectData::CITIZEN_PROJECT_1_UUID);
        $this->assertCount(1, $citizenProject->getApprovedCommitteeSupports());

        $this->client->request(Request::METHOD_GET, sprintf('/projets-citoyens/%s', $citizenProject->getSlug()));
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertSame('btn--as-link text--body text--blue--dark b__nudge--top', $crawler->filter('button#committee-confirm-support')->attr('class'));

        $this->client->submit($crawler->selectButton('Retirer mon soutien à ce projet')->form());
        $crawler = $this->client->followRedirect();
        $this->seeFlashMessage($crawler, sprintf('Votre comité %s ne soutient plus le projet citoyen %s',
            $committee->getName(),
            $citizenProject->getName()
        ));
    }

    public function testCitizenProjectContactActors()
    {
        $this->disableRepublicanSilence();

        // Authenticate as the administrator (host)
        $this->authenticateAsAdherent($this->client, 'lolodie.dutemps@hotnix.tld');
        $crawler = $this->client->request(Request::METHOD_GET, '/evenements');
        $crawler = $this->client->click($crawler->selectLink('En Marche - Projet citoyen')->link());
        $crawler = $this->client->click($crawler->selectLink('Voir')->link());

        $token = $crawler->filter('#members-contact-token')->attr('value');
        $uuids = (array) $crawler->filter('input[name="members[]"]')->attr('value');

        $actorsListUrl = $this->client->getRequest()->getPathInfo();
        $contactUrl = $actorsListUrl.'/contact';

        $crawler = $this->client->request(Request::METHOD_POST, $contactUrl, [
            'token' => $token,
            'contacts' => json_encode($uuids),
        ]);

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        // Try to post with an empty subject and an empty message
        $crawler = $this->client->request(Request::METHOD_POST, $contactUrl, [
            'token' => $crawler->filter('input[name="token"]')->attr('value'),
            'contacts' => $crawler->filter('input[name="contacts"]')->attr('value'),
            'subject' => ' ',
            'message' => ' ',
        ]);

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $this->assertSame('Cette valeur ne doit pas être vide.',
            $crawler->filter('.subject .form__errors > .form__error')->text()
        );

        $this->assertSame('Cette valeur ne doit pas être vide.',
            $crawler->filter('.message .form__errors > .form__error')->text()
        );

        $this->client->request(Request::METHOD_POST, $contactUrl, [
            'token' => $crawler->filter('input[name="token"]')->attr('value'),
            'contacts' => $crawler->filter('input[name="contacts"]')->attr('value'),
            'subject' => 'Bonsoir',
            'message' => 'Bonsoir à tous.',
        ]);

        $this->assertClientIsRedirectedTo($actorsListUrl, $this->client);
        $crawler = $this->client->followRedirect();
        $this->seeFlashMessage($crawler, 'Félicitations, votre message a bien été envoyé aux acteurs sélectionnés.');

        // Try to illegally contact an adherent, adds an adherent not linked with this citizen project
        $uuids[] = LoadAdherentData::ADHERENT_1_UUID;

        $crawler = $this->client->request(Request::METHOD_POST, $contactUrl, [
            'token' => $token,
            'contacts' => json_encode($uuids),
        ]);

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        // The protection filter should be remove the illegal adherent
        $this->assertCount(1, json_decode($crawler->filter('input[name="contacts"]')->attr('value'), true));

        // Force the contact form with the foreign uuid
        $this->client->request(Request::METHOD_POST, $contactUrl, [
            'token' => $crawler->filter('input[name="token"]')->attr('value'),
            'contacts' => json_encode($uuids),
            'subject' => 'Bonsoir',
            'message' => 'Bonsoir à tous.',
        ]);

        $this->assertClientIsRedirectedTo($actorsListUrl, $this->client);
        $crawler = $this->client->followRedirect();
        $this->seeFlashMessage($crawler, 'Félicitations, votre message a bien été envoyé aux acteurs sélectionnés.');
    }

    public function testAnonymousUserIsAllowedToFollowCitizenProject()
    {
        $committeeUrl = sprintf('/projets-citoyens/%s', '75008-le-projet-citoyen-a-paris-8');

        $crawler = $this->client->request(Request::METHOD_GET, $committeeUrl);

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertFalse($this->seeFollowLink($crawler));
        $this->assertFalse($this->seeUnfollowLink($crawler));
        $this->assertFalse($this->seeRegisterLink($crawler, 0));
        $this->assertTrue($this->seeLoginLink($crawler));
    }

    public function testAuthenticatedAdherentCanFollowCitizenProject()
    {
        $this->authenticateAsAdherent($this->client, 'benjyd@aol.com');

        // Browse to the citizen project details page
        $citizenProjectUrl = sprintf('/projets-citoyens/%s', '75008-le-projet-citoyen-a-paris-8');

        $crawler = $this->client->request(Request::METHOD_GET, $citizenProjectUrl);

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertContains('4 membres', $crawler->filter('#members .citizen-project__card__title')->text());
        $this->assertTrue($this->seeFollowLink($crawler));
        $this->assertFalse($this->seeUnfollowLink($crawler));
        $this->assertFalse($this->seeRegisterLink($crawler, 0));
        $this->assertFalse($this->seeLoginLink($crawler));

        // Emulate POST request to follow the committee.
        $token = $crawler->selectButton('Rejoindre ce projet')->attr('data-csrf-token');
        $this->client->request(Request::METHOD_POST, $citizenProjectUrl.'/rejoindre', ['token' => $token]);

        // Email sent to the host
        $this->assertCountMails(1, CitizenProjectNewFollowerMessage::class, 'jacques.picard@en-marche.fr');
        $this->assertMessageIsDispatched(CreateStaticSegmentCommand::class);
        $this->assertMessageIsDispatched(AddAdherentToStaticSegmentCommand::class);

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        // Refresh the committee details page
        $crawler = $this->client->request(Request::METHOD_GET, $citizenProjectUrl);

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertContains('5 membres', $crawler->filter('#members .citizen-project__card__title')->text());
        $this->assertFalse($this->seeFollowLink($crawler));
        $this->assertTrue($this->seeUnfollowLink($crawler));
        $this->assertFalse($this->seeRegisterLink($crawler, 0));
        $this->assertFalse($this->seeLoginLink($crawler));

        // Emulate POST request to unfollow the committee.
        $token = $crawler->selectButton('Quitter ce projet citoyen')->attr('data-csrf-token');
        $this->client->request(Request::METHOD_POST, $citizenProjectUrl.'/quitter', ['token' => $token]);

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertMessageIsDispatched(CreateStaticSegmentCommand::class);
        $this->assertMessageIsDispatched(RemoveAdherentFromStaticSegmentCommand::class);

        // Refresh the committee details page
        $crawler = $this->client->request(Request::METHOD_GET, $citizenProjectUrl);

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertContains('4 membres', $crawler->filter('#members .citizen-project__card__title')->text());
        $this->assertTrue($this->seeFollowLink($crawler));
        $this->assertFalse($this->seeUnfollowLink($crawler));
        $this->assertFalse($this->seeRegisterLink($crawler, 0));
        $this->assertFalse($this->seeLoginLink($crawler));
    }

    public function testFeaturedCitizenProject()
    {
        /** @var CitizenProject $citizenProject */
        $citizenProject = $this->getCitizenProjectRepository()->findOneByUuid(LoadCitizenProjectData::CITIZEN_PROJECT_1_UUID);
        $citizenProjectUrl = '/projets-citoyens/75008-le-projet-citoyen-a-paris-8';
        $crawler = $this->client->request(Request::METHOD_GET, $citizenProjectUrl);

        $this->assertFalse($citizenProject->isFeatured());
        $this->assertSame(0, $crawler->filter('.citizen_project_featured')->count());

        $citizenProject->setFeatured(true);

        $this->manager->flush();
        $this->manager->clear();

        $citizenProject = $this->getCitizenProjectRepository()->findOneByUuid(LoadCitizenProjectData::CITIZEN_PROJECT_1_UUID);

        $crawler = $this->client->request(Request::METHOD_GET, $citizenProjectUrl);

        $this->assertTrue($citizenProject->isFeatured());
        $this->assertSame(1, $crawler->filter('.citizen_project_featured')->count());
        $this->assertSame('Nos coups de cœur', trim($crawler->filter('.citizen_project_featured')->text()));
    }

    private function assertSeeCitizenActions(): void
    {
        $this->assertCount(2, $actions = $this->client->getCrawler()->filter('.citizen-project-actions ul'), 'There should be 2 next actions');
        $this->assertRegExp('~Projet citoyen Paris-18\n.+2 inscrit\(s\)~', $actions->first()->filter('li')->eq(1)->text());
        $this->assertRegExp('~Projet citoyen #3\n.+1 inscrit\(s\)~', $actions->last()->filter('li')->eq(1)->text());
    }

    private function assertSeeComments(array $comments): void
    {
        foreach ($comments as $position => $comment) {
            list($author, $text) = $comment;
            $this->assertSeeComment($position, $author, $text);
        }
    }

    private function assertSeeComment(int $position, string $author, string $text): void
    {
        $crawler = $this->client->getCrawler();
        $this->assertContains($author, $crawler->filter('.citizen-project-comment')->eq($position)->text());
        $this->assertContains($text, $crawler->filter('.citizen-project-comment div:nth-child(2) p')->eq($position)->text());
    }

    private function seeCommentSection(): bool
    {
        return 1 === \count($this->client->getCrawler()->filter('.citizen-project-comments'));
    }

    private function seeFollowLink(Crawler $crawler): bool
    {
        $button = $crawler->selectButton('Rejoindre ce projet');

        return $button->count() && !$button->attr('disabled');
    }

    private function seeUnfollowLink(Crawler $crawler): bool
    {
        return 1 === \count($crawler->filter('.citizen-project-unfollow'));
    }

    private function seeRegisterLink(Crawler $crawler, $nb = 1): bool
    {
        $this->assertCount($nb, $crawler->filter('.citizen-project-follow--disabled'));

        return 1 === \count($crawler->filter('#citizen-project-register-link'));
    }

    private function seeLoginLink(Crawler $crawler): bool
    {
        return 1 === $crawler->selectLink('Connectez-vous')->count();
    }

    private function seeReportLink(): bool
    {
        try {
            $this->client->getCrawler()->selectLink('Signaler un abus')->link();
        } catch (\InvalidArgumentException $e) {
            return false;
        }

        return true;
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

    protected function getGroupUrl(): string
    {
        return '/projets-citoyens/75008-le-projet-citoyen-a-paris-8';
    }
}
