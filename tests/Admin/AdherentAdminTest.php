<?php

namespace Tests\App\Admin;

use App\DataFixtures\ORM\LoadAdherentData;
use App\Entity\Adherent;
use App\Entity\MyTeam\DelegatedAccess;
use App\Entity\MyTeam\MyTeam;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\App\AbstractWebCaseTest;
use Tests\App\Controller\ControllerTestTrait;

/**
 * @group functional
 * @group admin
 */
class AdherentAdminTest extends AbstractWebCaseTest
{
    use ControllerTestTrait;

    private const ADHERENT_EDIT_URI_PATTERN = '/admin/app/adherent/%d/edit';

    private $adherentRepository;

    public function testAnAdminCantBanAnAdherent()
    {
        $this->authenticateAsAdmin($this->client);

        /** @var Adherent $adherent */
        $adherent = $this->adherentRepository->findOneByUuid(LoadAdherentData::ADHERENT_2_UUID);
        $crawler = $this->client->request(Request::METHOD_GET, sprintf(self::ADHERENT_EDIT_URI_PATTERN, $adherent->getId()));

        $navBar = $crawler->filter('ul.dropdown-menu > li');
        $this->assertEquals('Afficher', trim($navBar->getNode(0)->nodeValue));
        $this->assertEquals('Retourner à la liste', trim($navBar->getNode(1)->nodeValue));
        $this->assertEquals('Impersonnifier', trim($navBar->getNode(2)->nodeValue));

        $this->client->request(Request::METHOD_GET, sprintf('/admin/app/adherent/%s/ban', $adherent->getId()));
        $this->assertResponseStatusCode(403, $this->client->getResponse());
    }

    public function testEditBoardMemberInformations()
    {
        $adherent = $this->adherentRepository->findOneByUuid(LoadAdherentData::ADHERENT_2_UUID);
        $this->assertTrue($adherent->isBoardMember());
        $this->authenticateAsAdmin($this->client);
        $editUrl = sprintf(self::ADHERENT_EDIT_URI_PATTERN, $adherent->getId());
        // Empty roles should revoke board member
        $crawler = $this->client->request(Request::METHOD_GET, $editUrl);
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $form = $crawler->selectButton('Mettre à jour')->form();
        $formName = str_replace(
            sprintf('%s?uniqid=', $editUrl),
            '',
            $form->getFormNode()->getAttribute('action')
        );
        $form[sprintf('%s[boardMemberRoles][0]', $formName)]->untick();
        $this->client->submit($form);
        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());
        $this->get('doctrine.orm.entity_manager')->clear();
        $adherent = $this->adherentRepository->findOneByUuid(LoadAdherentData::ADHERENT_2_UUID);
        $this->assertFalse($adherent->isBoardMember());
        $this->assertNull($adherent->getBoardMember());
        // Fill only area should not grant board member
        $crawler = $this->client->request(Request::METHOD_GET, $editUrl);
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $form = $crawler->selectButton('Mettre à jour')->form();
        $formName = str_replace(
            sprintf('%s?uniqid=', $editUrl),
            '',
            $form->getFormNode()->getAttribute('action')
        );
        $form[sprintf('%s[boardMemberArea]', $formName)] = 'metropolitan';
        $this->client->submit($form);
        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());
        $this->get('doctrine.orm.entity_manager')->clear();
        $adherent = $this->adherentRepository->findOneByUuid(LoadAdherentData::ADHERENT_2_UUID);
        $this->assertFalse($adherent->isBoardMember());
        $this->assertNull($adherent->getBoardMember());
        // Fill only roles should not grant board member
        $crawler = $this->client->request(Request::METHOD_GET, $editUrl);
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $form = $crawler->selectButton('Mettre à jour')->form();
        $formName = str_replace(
            sprintf('%s?uniqid=', $editUrl),
            '',
            $form->getFormNode()->getAttribute('action')
        );
        $form[sprintf('%s[boardMemberRoles][0]', $formName)]->tick();
        $this->client->submit($form);
        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());
        $this->get('doctrine.orm.entity_manager')->clear();
        $adherent = $this->adherentRepository->findOneByUuid(LoadAdherentData::ADHERENT_2_UUID);
        $this->assertFalse($adherent->isBoardMember());
        $this->assertNull($adherent->getBoardMember());
        // Fill area and roles should grant board member
        $crawler = $this->client->request(Request::METHOD_GET, $editUrl);
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $form = $crawler->selectButton('Mettre à jour')->form();
        $formName = str_replace(
            sprintf('%s?uniqid=', $editUrl),
            '',
            $form->getFormNode()->getAttribute('action')
        );
        $form[sprintf('%s[boardMemberArea]', $formName)] = 'metropolitan';
        $form[sprintf('%s[boardMemberRoles][0]', $formName)]->tick();
        $this->client->submit($form);
        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());
        $this->get('doctrine.orm.entity_manager')->clear();
        $adherent = $this->adherentRepository->findOneByUuid(LoadAdherentData::ADHERENT_2_UUID);
        $this->assertTrue($adherent->isBoardMember());
    }

    public function testASuperAdminCanBanAnAdherent()
    {
        $this->authenticateAsAdmin($this->client, 'superadmin@en-marche-dev.fr');

        $this->client->followRedirects();

        /** @var Adherent $adherent */
        $adherent = $this->adherentRepository->findOneByUuid(LoadAdherentData::ADHERENT_19_UUID);
        $crawler = $this->client->request(Request::METHOD_GET, sprintf(self::ADHERENT_EDIT_URI_PATTERN, $adherent->getId()));

        $navBar = $crawler->filter('ul.dropdown-menu > li');
        $this->assertEquals('Afficher', trim($navBar->getNode(0)->nodeValue));
        $this->assertEquals('Retourner à la liste', trim($navBar->getNode(1)->nodeValue));
        $this->assertEquals('Impersonnifier', trim($navBar->getNode(2)->nodeValue));
        $this->assertEquals('Exclure cet adhérent ⚠️', trim($navBar->getNode(3)->nodeValue));
        $this->assertEquals('Certifier cet adhérent', trim($navBar->getNode(4)->nodeValue));

        $link = $crawler->selectLink('Exclure cet adhérent ⚠️')->link();
        $crawler = $this->client->click($link);

        $this->assertResponseStatusCode(200, $this->client->getResponse());

        $this->client->submit($crawler->selectButton('Confirmer')->form());

        $this->assertStringContainsString(sprintf('L\'adhérent <b>%s</b> a bien été exclu', $adherent->getFullName()), $this->client->getResponse()->getContent());
    }

    public function testAnAdminWithoutRoleCannotUpdateCustomInstanceQuality()
    {
        $this->authenticateAsAdmin($this->client);

        $adherent = $this->adherentRepository->findOneByUuid(LoadAdherentData::ADHERENT_19_UUID);
        $crawler = $this->client->request(Request::METHOD_GET, $editUrl = sprintf(self::ADHERENT_EDIT_URI_PATTERN, $adherent->getId()));
        $form = $crawler->selectButton('Mettre à jour')->form();
        $formName = str_replace(
            sprintf('%s?uniqid=', $editUrl),
            '',
            $form->getFormNode()->getAttribute('action')
        );
        self::assertFalse($form->has($formName.'[instanceQualities]'));
    }

    public function testAnSuperAdminCanUpdateCustomInstanceQuality()
    {
        $adherent = $this->adherentRepository->findOneByUuid(LoadAdherentData::ADHERENT_19_UUID);

        $this->authenticateAsAdherent($this->client, $adherent->getEmailAddress());
        $this->client->request('GET', '/conseil-national');
        $this->assertStatusCode(403, $this->client);

        $this->authenticateAsAdmin($this->client, 'superadmin@en-marche-dev.fr');

        $crawler = $this->client->request('GET', $editUrl = sprintf(self::ADHERENT_EDIT_URI_PATTERN, $adherent->getId()));

        $form = $crawler->selectButton('Mettre à jour')->form();
        $formName = str_replace(sprintf('%s?uniqid=', $editUrl), '', $form->getFormNode()->getAttribute('action'));

        $form[$formName.'[instanceQualities]'] = 9;
        $this->client->submit($form);
        $this->assertStatusCode(Response::HTTP_FOUND, $this->client);

        $this->authenticateAsAdherent($this->client, $adherent->getEmailAddress());

        $this->client->request('GET', '/conseil-national');
        $this->assertStatusCode(200, $this->client);
    }

    public function testCorrespondentDelegatedAccessChangedWhenAdherentLostAndRegainHisAccess()
    {
        /** @var Adherent $adherent */
        $adherent = $this->manager->getRepository(Adherent::class)->findOneByEmail('je-mengage-user-1@en-marche-dev.fr');

        $this->assertCount(1, $this->manager->getRepository(MyTeam::class)->findBy(['owner' => $adherent]));
        $this->assertCount(2, $this->manager->getRepository(DelegatedAccess::class)->findBy(['delegator' => $adherent]));

        $this->authenticateAsAdmin($this->client);

        $crawler = $this->client->request(Request::METHOD_GET, sprintf(self::ADHERENT_EDIT_URI_PATTERN, $adherent->getId()));

        $csrfInput = $crawler->filter('form input[id$=__token]')->first();
        $formName = str_replace('__token', '', $csrfInput->attr('id'));

        $form = $crawler->selectButton('Mettre à jour')->form();

        $values = $form->getPhpValues()[$formName];
        $values['lastName'] = 'Fullstackk';
        $values['zoneBasedRoles'] = [];

        $this->client->request($form->getMethod(), $form->getUri(), [$formName => $values]);

        $this->assertClientIsRedirectedTo(sprintf(self::ADHERENT_EDIT_URI_PATTERN, $adherent->getId()), $this->client);

        $crawler = $this->client->followRedirect();

        $errors = $crawler->filter('.sonata-ba-field-error-messages > li');
        $error = $crawler->filter('.alert-danger');

        $this->assertSame(0, $errors->count());
        $this->assertSame(0, $error->count());
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertCount(1, $this->manager->getRepository(MyTeam::class)->findBy(['owner' => $adherent]));
        $this->assertCount(0, $this->manager->getRepository(DelegatedAccess::class)->findBy(['delegator' => $adherent]));

        // regain role
        $crawler = $this->client->request(Request::METHOD_GET, sprintf(self::ADHERENT_EDIT_URI_PATTERN, $adherent->getId()));

        $csrfInput = $crawler->filter('form input[id$=__token]')->first();
        $formName = str_replace('__token', '', $csrfInput->attr('id'));

        $form = $crawler->selectButton('Mettre à jour')->form();

        $values = $form->getPhpValues()[$formName];
        $values['lastName'] = 'Fullstackk';
        $values['zoneBasedRoles'] = [[
            'type' => 'correspondent',
            'zones' => [291],
        ]];

        $this->client->request($form->getMethod(), $form->getUri(), [$formName => $values]);

        $this->assertClientIsRedirectedTo(sprintf(self::ADHERENT_EDIT_URI_PATTERN, $adherent->getId()), $this->client);

        $crawler = $this->client->followRedirect();

        $errors = $crawler->filter('.sonata-ba-field-error-messages > li');
        $error = $crawler->filter('.alert-danger');

        $this->assertSame(0, $errors->count());
        $this->assertSame(0, $error->count());
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertCount(1, $myTeams = $this->manager->getRepository(MyTeam::class)->findBy(['owner' => $adherent]));
        $this->assertCount(current($myTeams)->getMembers()->count(), $this->manager->getRepository(DelegatedAccess::class)->findBy(['delegator' => $adherent]));
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->adherentRepository = $this->getAdherentRepository();
    }

    protected function tearDown(): void
    {
        $this->adherentRepository = null;

        parent::tearDown();
    }
}
