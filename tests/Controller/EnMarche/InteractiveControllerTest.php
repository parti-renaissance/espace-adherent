<?php

namespace Tests\AppBundle\Controller\EnMarche;

use AppBundle\DataFixtures\ORM\LoadHomeBlockData;
use AppBundle\DataFixtures\ORM\LoadPurchasingPowerData;
use AppBundle\Entity\InteractiveChoice;
use AppBundle\Interactive\InteractiveProcessor;
use AppBundle\Repository\EmailRepository;
use AppBundle\Repository\InteractiveChoiceRepository;
use AppBundle\Repository\InteractiveInvitationRepository;
use AppBundle\Interactive\InteractiveProcessorHandler;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\AppBundle\Controller\ControllerTestTrait;
use Tests\AppBundle\SqliteWebTestCase;

/**
 * @group functional
 */
class InteractiveControllerTest extends SqliteWebTestCase
{
    use ControllerTestTrait;

    const PURCHASING_POWER_PATH = '/interactif/ton-pouvoir-achat';
    const PURCHASING_POWER_RESTART_PATH = '/interactif/ton-pouvoir-achat/recommencer';
    const PURCHASING_POWER_SENT_PATH = '/interactif/ton-pouvoir-achat/%s/merci';

    /* @var InteractiveChoiceRepository */
    private $purchasingPowerChoiceRepository;

    /* @var InteractiveInvitationRepository */
    private $purchasingPowerInvitationRepository;

    /* @var EmailRepository */
    private $emailRepository;

    public function testInteractiveAction()
    {
        $this->assertCount(0, $this->emailRepository->findAll());

        $crawler = $this->client->request(Request::METHOD_GET, self::PURCHASING_POWER_PATH);

        $this->assertEmpty($this->getCurrentInteractive()->friendFirstName);
        $this->assertEmpty($this->getCurrentInteractive()->friendAge);
        $this->assertEmpty($this->getCurrentInteractive()->friendGender);
        $this->assertEmpty($this->getCurrentInteractive()->friendPosition);

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertCount(1, $crawler->filter('button[name="interactive[fill_info]"]'));

        $this->client->submit($crawler->filter('form[name="interactive"]')->form([
            'interactive[friendFirstName]' => 'Mylène',
            'interactive[friendAge]' => '26',
            'interactive[friendGender]' => 'female',
            'interactive[friendPosition]' => '5',
        ]));

        $this->assertSame('Mylène', $this->getCurrentInteractive()->friendFirstName);
        $this->assertSame(26, $this->getCurrentInteractive()->friendAge);
        $this->assertSame('female', $this->getCurrentInteractive()->friendGender);
        $this->assertInstanceOf(InteractiveChoice::class, $this->getCurrentInteractive()->friendPosition);
        $this->assertEquals('Salarié de la fonction publique', $this->getCurrentInteractive()->friendPosition->getLabel());
        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());
        $this->assertClientIsRedirectedTo(self::PURCHASING_POWER_PATH, $this->client);
    }

    public function testRestartInteractiveAction()
    {
        $crawler = $this->client->request(Request::METHOD_GET, self::PURCHASING_POWER_PATH);

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $this->client->submit($crawler->filter('form[name="interactive"]')->form([
            'interactive[friendFirstName]' => 'Mylène',
            'interactive[friendAge]' => '26',
            'interactive[friendGender]' => 'female',
            'interactive[friendPosition]' => '5',
        ]));

        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());
        $this->assertClientIsRedirectedTo(self::PURCHASING_POWER_PATH, $this->client);

        $this->client->followRedirect();
        $this->client->request(Request::METHOD_GET, self::PURCHASING_POWER_RESTART_PATH);

        $this->assertNull($this->client->getRequest()->getSession()->get(InteractiveProcessorHandler::SESSION_KEY));
    }

    protected function setUp()
    {
        parent::setUp();

        $this->init([
            LoadHomeBlockData::class,
            LoadPurchasingPowerData::class,
        ]);

        $this->purchasingPowerChoiceRepository = $this->getInteractiveChoiceRepository();
        $this->purchasingPowerInvitationRepository = $this->getInteractiveInvitationRepository();
        $this->emailRepository = $this->getEmailRepository();
    }

    protected function tearDown()
    {
        $this->kill();

        $this->emailRepository = null;
        $this->purchasingPowerInvitationRepository = null;
        $this->purchasingPowerChoiceRepository = null;

        parent::tearDown();
    }

    private function getInteractiveInvitationHandler(): InteractiveProcessorHandler
    {
        return $this->container->get('app.interactive.interactive_processor_handler');
    }

    private function getCurrentInteractive(): InteractiveProcessor
    {
        return $this->getInteractiveInvitationHandler()->start($this->client->getRequest()->getSession());
    }

    private function getChoice(int $id): ?InteractiveChoice
    {
        return $this->purchasingPowerChoiceRepository->find($id);
    }

    /**
     * @param int[] $ids
     *
     * @return InteractiveChoice[]|ArrayCollection|array
     */
    private function getChoices(array $ids, bool $asCollection = false): iterable
    {
        $choices = $this->purchasingPowerChoiceRepository->findBy(['id' => $ids]);

        return $asCollection ? new ArrayCollection($choices) : $choices;
    }
}
