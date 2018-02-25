<?php

namespace Tests\AppBundle\Procuration;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\ProcurationProxy;
use AppBundle\Entity\ProcurationRequest;
use AppBundle\Mailer\Message\ProcurationProxyCancelledMessage;
use AppBundle\Mailer\Message\ProcurationProxyFoundMessage;
use AppBundle\Procuration\ProcurationProxyMessageFactory;
use AppBundle\Routing\RemoteUrlGenerator;
use libphonenumber\PhoneNumberUtil;
use PHPUnit\Framework\TestCase;

class ProcurationProxyMessageFactoryTest extends TestCase
{
    /**
     * @var RemoteUrlGenerator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $urlGenerator;

    /**
     * @var ProcurationProxyMessageFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $factory;

    protected function setUp()
    {
        parent::setUp();

        $this->urlGenerator = $this->createMock(RemoteUrlGenerator::class);
        $this->factory = new ProcurationProxyMessageFactory($this->urlGenerator, 'procurations@en-marche-dev.fr');
    }

    protected function tearDown()
    {
        $this->urlGenerator = null;
        $this->factory = null;

        parent::tearDown();
    }

    public function testCreateProxyCancelledMessage()
    {
        $this->urlGenerator->expects($this->never())->method('generate');

        $request = $this->createProcurationRequestMock('Marie Bénédicte', 'Dumont', 'marieb.dumont@gmail.tld');
        $request
            ->expects($this->once())
            ->method('getFoundProxy')
            ->willReturn($this->createProcurationProxyMock('Monique', 'Clairefontaine', 'monique@en-marche-dev.fr'))
        ;

        $message = $this->factory->createProxyCancelledMessage($request, $this->createAdherentMock('john@smith.tld'));

        $this->assertInstanceOf(ProcurationProxyCancelledMessage::class, $message);
    }

    public function testCreateProxyFoundMessage()
    {
        $this->urlGenerator
            ->expects($this->once())
            ->method('generate')
            ->willReturn('https://enmarche.code/procuration/3/foo-bar')
        ;

        $request = $this->createProcurationRequestMock('Marie Bénédicte', 'Dumont', 'marieb.dumont@gmail.tld', '0102030405');
        $request
            ->expects($this->once())
            ->method('getFoundProxy')
            ->willReturn($this->createProcurationProxyMock('Monique', 'Clairefontaine', 'monique@en-marche-dev.fr', '0607080910'))
        ;
        $request
            ->expects($this->once())
            ->method('getFoundBy')
            ->willReturn($this->createAdherentMock('john@smith.tld'))
        ;

        $message = $this->factory->createProxyFoundMessage($request);

        $this->assertInstanceOf(ProcurationProxyFoundMessage::class, $message);
    }

    private function createProcurationRequestMock(string $firstNames, string $lastName, string $email, string $phone = '')
    {
        $request = $this->createMock(ProcurationRequest::class);
        $request->expects($this->any())->method('getFirstNames')->willReturn($firstNames);
        $request->expects($this->any())->method('getLastName')->willReturn($lastName);
        $request->expects($this->any())->method('getEmailAddress')->willReturn($email);

        if ($phone) {
            $phoneUtil = PhoneNumberUtil::getInstance();
            $request->expects($this->any())->method('getPhone')->willReturn($phoneUtil->parse($phone, 'FR'));
        }

        return $request;
    }

    private function createProcurationProxyMock(string $firstNames, string $lastName, string $email, string $phone = '')
    {
        $proxy = $this->createMock(ProcurationProxy::class);
        $proxy->expects($this->any())->method('getFirstNames')->willReturn($firstNames);
        $proxy->expects($this->any())->method('getLastName')->willReturn($lastName);
        $proxy->expects($this->any())->method('getEmailAddress')->willReturn($email);

        if ($phone) {
            $phoneUtil = PhoneNumberUtil::getInstance();
            $proxy->expects($this->any())->method('getPhone')->willReturn($phoneUtil->parse($phone, 'FR'));
        }

        return $proxy;
    }

    private function createAdherentMock(string $email)
    {
        $adherent = $this->createMock(Adherent::class);
        $adherent->expects($this->any())->method('getEmailAddress')->willReturn($email);

        return $adherent;
    }
}
