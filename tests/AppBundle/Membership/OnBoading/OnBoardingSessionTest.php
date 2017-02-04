<?php

namespace AppBundle\Membership\OnBoading;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\PostAddress;
use AppBundle\Membership\OnBoarding\OnBoardingSession;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

class OnBoardingSessionTest extends \PHPUnit_Framework_TestCase
{
    /** @var Session */
    private $session;

    public function testStart()
    {
        $session = new OnBoardingSession($this->session);

        $this->assertNull($this->session->get(OnBoardingSession::NEW_ADHERENT));

        $session->start($this->getAdherent());

        $this->assertSame('OK', $this->session->get(OnBoardingSession::NEW_ADHERENT));
    }

    public function testIsStarted()
    {
        $session = new OnBoardingSession($this->session);

        $this->assertFalse($session->isStarted());

        $this->session->set(OnBoardingSession::NEW_ADHERENT, null);

        $this->assertTrue($session->isStarted());
    }

    public function testTerminate()
    {
        $session = new OnBoardingSession($this->session);

        $this->session->set(OnBoardingSession::NEW_ADHERENT, null);

        $this->assertTrue($this->session->has(OnBoardingSession::NEW_ADHERENT));

        $session->terminate();

        $this->assertFalse($this->session->has(OnBoardingSession::NEW_ADHERENT));
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
                return 'OK';
            }
        };
    }
}
