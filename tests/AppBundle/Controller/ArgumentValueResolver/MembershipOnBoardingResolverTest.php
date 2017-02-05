<?php

namespace Test\AppBundle\Controller\ArgumentValueResolver;

use AppBundle\Controller\ArgumentValueResolver\MembershipOnBoardingResolver;
use AppBundle\Donation\DonationRequest;
use AppBundle\Donation\DonationRequestFactory;
use AppBundle\Entity\Adherent;
use AppBundle\Membership\OnBoarding\OnBoardingAdherent;
use AppBundle\Membership\OnBoarding\OnBoardingDonation;
use AppBundle\Membership\OnBoarding\OnBoardingSessionHandler;
use AppBundle\Repository\AdherentRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

class MembershipOnBoardingResolverTest extends \PHPUnit_Framework_TestCase
{
    /** @var MembershipOnBoardingResolver */
    private $resolver;

    /** @var AdherentRepository|\PHPUnit_Framework_MockObject_MockObject */
    private $adherentRepository;

    /** @var DonationRequestFactory */
    private $donationRequestFactory;

    public function testSupportsWithoutSession()
    {
        $this->assertFalse(
            $this->resolver->supports(Request::create('/'), $this->createArgumentMetadata())
        );

        $this->assertFalse(
            $this->resolver->supports(Request::create('/'), $this->createArgumentMetadata(OnBoardingAdherent::class))
        );

        $this->assertFalse(
            $this->resolver->supports(Request::create('/'), $this->createArgumentMetadata(OnBoardingDonation::class))
        );
    }

    public function testSupports()
    {
        $this->assertFalse(
            $this->resolver->supports($this->createRequest(), $this->createArgumentMetadata())
        );

        $this->assertTrue(
            $this->resolver->supports($this->createRequest(), $this->createArgumentMetadata(OnBoardingAdherent::class))
        );

        $this->assertTrue(
            $this->resolver->supports($this->createRequest(), $this->createArgumentMetadata(OnBoardingDonation::class))
        );
    }

    /**
     * @expectedException \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @expectedExceptionMessage The adherent has not been successfully redirected from the registration page.
     */
    public function testResolveWithNoAdherentId()
    {
        $this->adherentRepository
            ->expects($this->never())
            ->method('find');

        foreach ($this->resolver->resolve($this->createRequest(), $this->createArgumentMetadata()) as $result) {
        }
    }

    /**
     * @expectedException \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @expectedExceptionMessage New adherent not found for id "1".
     */
    public function testResolveWithWrongAdherentId()
    {
        $wrongID = 1;

        $this->adherentRepository
            ->expects($this->once())
            ->method('find')
            ->with($wrongID)
            ->willReturn(null);

        foreach ($this->resolver->resolve($this->createRequest($wrongID), $this->createArgumentMetadata()) as $result) {
        }
    }

    public function testResolveWithRegisteringAdherent()
    {
        $newAdherentId = 1;
        $adherent = $this->getMockBuilder(Adherent::class)->disableOriginalConstructor()->getMock();

        $this->adherentRepository
            ->expects($this->once())
            ->method('find')
            ->with($newAdherentId)
            ->willReturn($adherent);

        $results = $this->resolver->resolve(
            $this->createRequest($newAdherentId),
            $this->createArgumentMetadata(OnBoardingAdherent::class)
        );

        foreach ($results as $result) {
            $this->assertInstanceOf(OnBoardingAdherent::class, $result);
            $this->assertSame($adherent, $result->getAdherent());
        }
    }

    public function testResolveWithRegisteringDonation()
    {
        $newAdherentId = 1;

        $this->adherentRepository
            ->expects($this->once())
            ->method('find')
            ->with($newAdherentId)
            ->willReturn($this->getMockBuilder(Adherent::class)->disableOriginalConstructor()->getMock());

        $results = $this->resolver->resolve(
            $this->createRequest($newAdherentId),
            $this->createArgumentMetadata(OnBoardingDonation::class)
        );

        foreach ($results as $result) {
            $this->assertInstanceOf(OnBoardingDonation::class, $result);
            $this->assertInstanceOf(DonationRequest::class, $result->getDonationRequest());
        }
    }

    public function setUp()
    {
        parent::setUp();

        $this->adherentRepository = $this->getMockBuilder(AdherentRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->donationRequestFactory = new DonationRequestFactory();

        $this->resolver = new MembershipOnBoardingResolver(
            new OnBoardingSessionHandler(),
            $this->adherentRepository,
            $this->donationRequestFactory
        );
    }

    public function tearDown()
    {
        $this->adherentRepository = null;
        $this->donationRequestFactory = null;

        $this->resolver = null;

        parent::tearDown();
    }

    private function createArgumentMetadata(string $type = ''): ArgumentMetadata
    {
        return new ArgumentMetadata('arg', $type, false, false, null);
    }

    private function createRequest(?int $newAdherentId = null): Request
    {
        $request = Request::create('/');
        $session = new Session(new MockArraySessionStorage());

        if (null !== $newAdherentId) {
            $session->set(OnBoardingSessionHandler::NEW_ADHERENT, $newAdherentId);
        }

        $request->setSession($session);

        return $request;
    }
}
