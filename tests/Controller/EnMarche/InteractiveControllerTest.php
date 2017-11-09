<?php

namespace Tests\AppBundle\Controller\EnMarche;

use AppBundle\DataFixtures\ORM\LoadHomeBlockData;
use AppBundle\DataFixtures\ORM\LoadPurchasingPowerData;
use AppBundle\Entity\PurchasingPowerChoice;
use AppBundle\Repository\MailjetEmailRepository;
use AppBundle\Repository\PurchasingPowerChoiceRepository;
use AppBundle\Repository\PurchasingPowerInvitationRepository;
use AppBundle\Interactive\PurchasingPowerProcessor;
use AppBundle\Interactive\PurchasingPowerProcessorHandler;
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

    const PURCHASING_POWER_PATH = '/ton-pouvoir-achat';
    const PURCHASING_POWER_RESTART_PATH = '/ton-pouvoir-achat/recommencer';
    const PURCHASING_POWER_SENT_PATH = '/ton-pouvoir-achat/%s/merci';

    /* @var PurchasingPowerChoiceRepository */
    private $PurchasingPowerChoiceRepository;

    /* @var PurchasingPowerInvitationRepository */
    private $PurchasingPowerInvitationRepository;

    /* @var MailjetEmailRepository */
    private $emailRepository;

    public function testPurchasingPowerAction()
    {
        $this->assertCount(0, $this->emailRepository->findAll());

        $purchasingPower = new PurchasingPowerProcessor();

        $crawler = $this->client->request(Request::METHOD_GET, self::PURCHASING_POWER_PATH);

        $this->assertEquals($purchasingPower, $this->getCurrentPurchasingPower());
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertCount(1, $crawler->filter('button[name="purchasing_power[fill_info]"]'));

        $this->client->submit($crawler->filter('form[name="purchasing_power"]')->form([
            'purchasing_power[friendFirstName]' => $purchasingPower->friendFirstName = 'Mylène',
            'purchasing_power[friendAge]' => '26',
            'purchasing_power[friendGender]' => $purchasingPower->friendGender = 'female',
            'purchasing_power[friendPosition]' => '5',
        ]));

        $purchasingPower->friendAge = 26;
        $purchasingPower->friendPosition = $this->getChoice(5);
        $purchasingPower->marking = PurchasingPowerProcessor::STATE_NEEDS_FRIEND_CASES;

        $this->assertEquals($purchasingPower, $this->getCurrentPurchasingPower());
        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());
        $this->assertClientIsRedirectedTo(self::PURCHASING_POWER_PATH, $this->client);
    }

    public function testRestartPurchasingPowerAction()
    {
        $crawler = $this->client->request(Request::METHOD_GET, self::PURCHASING_POWER_PATH);

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $this->client->submit($crawler->filter('form[name="purchasing_power"]')->form([
            'purchasing_power[friendFirstName]' => 'Mylène',
            'purchasing_power[friendAge]' => '26',
            'purchasing_power[friendGender]' => 'female',
            'purchasing_power[friendPosition]' => '5',
        ]));

        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());
        $this->assertClientIsRedirectedTo(self::PURCHASING_POWER_PATH, $this->client);

        $this->client->followRedirect();
        $this->client->request(Request::METHOD_GET, self::PURCHASING_POWER_RESTART_PATH);

        $this->assertNull($this->client->getRequest()->getSession()->get(PurchasingPowerProcessorHandler::SESSION_KEY));
    }

    protected function setUp()
    {
        parent::setUp();

        $this->init([
            LoadHomeBlockData::class,
            LoadPurchasingPowerData::class,
        ]);

        $this->PurchasingPowerChoiceRepository = $this->getPurchasingPowerChoiceRepository();
        $this->PurchasingPowerInvitationRepository = $this->getPurchasingPowerInvitationRepository();
        $this->emailRepository = $this->getMailjetEmailRepository();
    }

    protected function tearDown()
    {
        $this->kill();

        $this->emailRepository = null;
        $this->PurchasingPowerInvitationRepository = null;
        $this->PurchasingPowerChoiceRepository = null;

        parent::tearDown();
    }

    private function getPurchasingPowerInvitationHandler(): PurchasingPowerProcessorHandler
    {
        return $this->container->get('app.interactive.purchasing_power_processor_handler');
    }

    private function getCurrentPurchasingPower(): PurchasingPowerProcessor
    {
        return $this->getPurchasingPowerInvitationHandler()->start($this->client->getRequest()->getSession());
    }

    private function getChoice(int $id): ?PurchasingPowerChoice
    {
        return $this->PurchasingPowerChoiceRepository->find($id);
    }

    /**
     * @param int[] $ids
     *
     * @return PurchasingPowerChoice[]|ArrayCollection|array
     */
    private function getChoices(array $ids, bool $asCollection = false): iterable
    {
        $choices = $this->PurchasingPowerChoiceRepository->findBy(['id' => $ids]);

        return $asCollection ? new ArrayCollection($choices) : $choices;
    }
}
