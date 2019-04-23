<?php

namespace Tests\AppBundle\Controller\EnMarche;

use AppBundle\Controller\EnMarche\InteractiveController;
use AppBundle\Interactive\MyEuropeProcessor;
use AppBundle\Interactive\MyEuropeProcessorHandler;
use AppBundle\Repository\EmailRepository;
use AppBundle\Repository\MyEuropeChoiceRepository;
use AppBundle\Repository\MyEuropeInvitationRepository;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\AppBundle\Controller\ControllerTestTrait;

/**
 * @group functional
 */
class InteractiveControllerTest extends WebTestCase
{
    use ControllerTestTrait;

    const MY_EUROPE_PATH = '/mon-europe';
    const MY_EUROPE_RESTART_PATH = '/mon-europe/recommencer';
    const MY_EUROPE_SENT_PATH = '/mon-europe/%s/merci';

    /* @var MyEuropeChoiceRepository */
    private $MyEuropeChoiceRepository;

    /* @var MyEuropeInvitationRepository */
    private $MyEuropeInvitationRepository;

    /* @var EmailRepository */
    private $emailRepository;

    public function testMyEuropeAction()
    {
        $this->assertCount(0, $this->emailRepository->findAll());

        $myEurope = new MyEuropeProcessor();

        $crawler = $this->client->request(Request::METHOD_GET, self::MY_EUROPE_PATH);

        $this->assertEquals($myEurope, $this->getCurrentMyEurope());
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertCount(1, $crawler->filter('button[name="my_europe[fill_info]"]'));

        $this->client->submit($crawler->filter('form[name="my_europe"]')->form([
            'my_europe[friendFirstName]' => $myEurope->friendFirstName = 'Mylène',
            'my_europe[friendAge]' => '26',
            'my_europe[friendGender]' => $myEurope->friendGender = 'female',
        ]));

        $myEurope->friendAge = 26;
        $myEurope->marking = MyEuropeProcessor::STATE_NEEDS_FRIEND_CASES;
        $myEurope->messageSubject = InteractiveController::MESSAGE_SUBJECT;

        $this->assertEquals($myEurope, $this->getCurrentMyEurope());
        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());
        $this->assertClientIsRedirectedTo(self::MY_EUROPE_PATH, $this->client);
    }

    public function testRestartMyEuropeAction()
    {
        $crawler = $this->client->request(Request::METHOD_GET, self::MY_EUROPE_PATH);

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $this->client->submit($crawler->filter('form[name="my_europe"]')->form([
            'my_europe[friendFirstName]' => 'Mylène',
            'my_europe[friendAge]' => '26',
            'my_europe[friendGender]' => 'female',
        ]));

        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());
        $this->assertClientIsRedirectedTo(self::MY_EUROPE_PATH, $this->client);

        $this->client->followRedirect();
        $this->client->request(Request::METHOD_GET, self::MY_EUROPE_RESTART_PATH);

        $this->assertNull($this->client->getRequest()->getSession()->get(MyEuropeProcessorHandler::SESSION_KEY));
    }

    protected function setUp()
    {
        parent::setUp();

        $this->init();

        $this->MyEuropeChoiceRepository = $this->getMyEuropeChoiceRepository();
        $this->MyEuropeInvitationRepository = $this->getMyEuropeInvitationRepository();
        $this->emailRepository = $this->getEmailRepository();
    }

    protected function tearDown()
    {
        $this->kill();

        $this->emailRepository = null;
        $this->MyEuropeInvitationRepository = null;
        $this->MyEuropeChoiceRepository = null;

        parent::tearDown();
    }

    private function getMyEuropeInvitationHandler(): MyEuropeProcessorHandler
    {
        return $this->container->get('app.interactive.my_europe_processor_handler');
    }

    private function getCurrentMyEurope(): MyEuropeProcessor
    {
        return $this->getMyEuropeInvitationHandler()->start($this->client->getRequest()->getSession());
    }
}
