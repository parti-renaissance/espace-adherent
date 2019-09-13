<?php

namespace Tests\AppBundle\Procuration;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\ProcurationProxy;
use AppBundle\Entity\ProcurationRequest;
use AppBundle\Mailer\Message\ProcurationProxyCancelledMessage;
use AppBundle\Mailer\Message\ProcurationProxyRegistrationConfirmationMessage;
use AppBundle\Mailer\Message\ProcurationRequestRegistrationConfirmationMessage;
use AppBundle\Procuration\ProcurationProxyMessageFactory;
use AppBundle\Routing\RemoteUrlGenerator;
use libphonenumber\PhoneNumberUtil;
use PHPUnit\Framework\TestCase;

/**
 * @group procuration
 */
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
        $this->assertSame('procurations@en-marche-dev.fr', $message->getReplyTo());
        $this->assertSame('Annulation de la mise en relation', $message->getSubject());
        $this->assertSame('marieb.dumont@gmail.tld', $message->getRecipient(0)->getEmailAddress());
        $this->assertNull($message->getRecipient(0)->getFullName());
        $this->assertSame('La République En Marche !', $message->getSenderName());
        $this->assertSame(
            [
                'target_firstname' => 'Marie Bénédicte',
                'voter_first_name' => 'Monique',
                'voter_last_name' => 'Clairefontaine',
            ],
            $message->getVars()
        );
        $this->assertSame(
            [
                'monique@en-marche-dev.fr',
            ],
            $message->getCC()
        );
        $this->assertSame(
            [
                'john@smith.tld',
            ],
            $message->getBCC()
        );
    }

    public function testCreateProxyFoundMessage()
    {
        $url = 'https://en-marche.fr/procuration/3/2839f66263bca70ff077d8e47fbdf783';

        $this->urlGenerator
            ->expects($this->once())
            ->method('generate')
            ->willReturn($url)
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

        $this->assertSame('procurations@en-marche-dev.fr', $message->getReplyTo());
        $this->assertSame('Votre procuration', $message->getSubject());
        $this->assertSame('marieb.dumont@gmail.tld', $message->getRecipient(0)->getEmailAddress());
        $this->assertNull($message->getRecipient(0)->getFullName());
        $this->assertSame('La République En Marche !', $message->getSenderName());
        $this->assertSame(
            [
                'info_link' => $url,
                'elections' => '',
                'voter_first_name' => 'Monique',
                'voter_last_name' => 'Clairefontaine',
                'voter_phone' => '+33 6 07 08 09 10',
                'mandant_first_name' => 'Marie Bénédicte',
                'mandant_last_name' => 'Dumont',
                'mandant_phone' => '+33 1 02 03 04 05',
            ],
            $message->getVars()
        );
        $this->assertSame(
            [
                'john@smith.tld',
                'monique@en-marche-dev.fr',
            ],
            $message->getCC()
        );
    }

    public function testCreateProxyRegistrationMessage()
    {
        $message = $this->factory->createProxyRegistrationMessage(
            $this->createProcurationProxyMock(
                'Monique',
                'Clairefontaine',
                'monique@en-marche-dev.fr',
                '0607080910'
            )
        );

        $this->assertInstanceOf(ProcurationProxyRegistrationConfirmationMessage::class, $message);
        $this->assertSame('procurations@en-marche-dev.fr', $message->getReplyTo());
        $this->assertSame('Vous souhaitez être mandataire', $message->getSubject());
        $this->assertSame('monique@en-marche-dev.fr', $message->getRecipient(0)->getEmailAddress());
        $this->assertSame('La République En Marche !', $message->getSenderName());
    }

    public function testCreateRequestRegistrationMessage()
    {
        $message = $this->factory->createRequestRegistrationMessage(
            $this->createProcurationRequestMock(
                'Marie Bénédicte',
                'Dumont',
                'marieb.dumont@gmail.tld',
                '0102030405'
            )
        );

        $this->assertInstanceOf(ProcurationRequestRegistrationConfirmationMessage::class, $message);
        $this->assertSame('procurations@en-marche-dev.fr', $message->getReplyTo());
        $this->assertSame('Vous souhaitez trouver un mandataire pour les élections européennes', $message->getSubject());
        $this->assertSame('marieb.dumont@gmail.tld', $message->getRecipient(0)->getEmailAddress());
        $this->assertSame('La République En Marche !', $message->getSenderName());
    }

    private function createProcurationRequestMock(
        string $firstNames,
        string $lastName,
        string $email,
        string $phone = ''
    ) {
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
