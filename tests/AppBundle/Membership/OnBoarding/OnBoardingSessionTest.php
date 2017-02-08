<?php

namespace Tests\AppBundle\Membership\OnBoarding;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\PostAddress;
use AppBundle\Membership\OnBoarding\OnBoardingSessionHandler;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

class OnBoardingSessionTest extends \PHPUnit_Framework_TestCase
{
    /** @var Session */
    private $session;

    public function testStart()
    {
        $session = new OnBoardingSessionHandler();

        $this->assertNull($this->session->get(OnBoardingSessionHandler::NEW_ADHERENT));

        $session->start($this->session, $this->getAdherent());

        $this->assertSame(1, $this->session->get(OnBoardingSessionHandler::NEW_ADHERENT));
    }

    public function testIsStarted()
    {
        $session = new OnBoardingSessionHandler();

        $this->assertFalse($session->isStarted($this->session));

        $this->session->set(OnBoardingSessionHandler::NEW_ADHERENT, null);

        $this->assertTrue($session->isStarted($this->session));
    }

    public function testGetNewAdherentId()
    {
        $session = new OnBoardingSessionHandler();
        $adherentId = 1;

        $this->assertNull($session->getNewAdherentId($this->session));

        $this->session->set(OnBoardingSessionHandler::NEW_ADHERENT, $adherentId);

        $this->assertSame($adherentId, $session->getNewAdherentId($this->session));
    }

    public function testTerminate()
    {
        $session = new OnBoardingSessionHandler();

        $this->session->set(OnBoardingSessionHandler::NEW_ADHERENT, null);

        $this->assertTrue($this->session->has(OnBoardingSessionHandler::NEW_ADHERENT));

        $session->terminate($this->session);

        $this->assertFalse($this->session->has(OnBoardingSessionHandler::NEW_ADHERENT));
    }

    public function setUp()
    {
        parent::setUp();

        $this->session = new Session(new MockArraySessionStorage());
    }

    protected function tearDown()
    {
        $this->session = null;

        parent::tearDown();
    }

    private function getAdherent()
    {
        return new class() extends Adherent {
            public function __construct()
            {
                parent::__construct(parent::createUuid('email'), 'email', '***', 'male', 'J', 'P', new \DateTime('31 years ago'), '', PostAddress::createFrenchAddress('street', '75009-75109'));
            }

            public function getId()
            {
                return 1;
            }
        };
    }
}
