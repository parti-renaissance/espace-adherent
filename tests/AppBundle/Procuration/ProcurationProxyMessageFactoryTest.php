<?php

namespace Tests\AppBundle\Procuration;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\ProcurationProxy;
use AppBundle\Entity\ProcurationRequest;
use AppBundle\Mailjet\Message\ProcurationProxyCancelledMessage;
use AppBundle\Mailjet\Message\ProcurationProxyFoundMessage;
use AppBundle\Procuration\ProcurationProxyMessageFactory;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ProcurationProxyMessageFactoryTest extends \PHPUnit_Framework_TestCase
{
    private $urlGenerator;

    /**
     * @var ProcurationProxyMessageFactory
     */
    private $factory;

    public function testCreateProxyCancelledMessage()
    {
        $this->urlGenerator->expects($this->never())->method('generate');

        $message = $this->factory->createProxyCancelledMessage(
            $this->createAdherentMock('john@smith.tld'),
            $this->createProcurationRequestMock('Marie Bénédicte', 'Dumont', 'marieb.dumont@gmail.tld'),
            $this->createProcurationProxyMock('Monique', 'Clairefontaine', 'monique@en-marche-dev.fr')
        );

        $this->assertInstanceOf(ProcurationProxyCancelledMessage::class, $message);
        $this->assertSame('procurations@en-marche-dev.fr', $message->getReplyTo());
        $this->assertSame('Annulation de la mise en relation', $message->getSubject());
        $this->assertSame('marieb.dumont@gmail.tld', $message->getRecipient(0)->getEmailAddress());
        $this->assertSame('Marie Bénédicte Dumont', $message->getRecipient(0)->getFullName());
        $this->assertSame('Procuration Macron', $message->getSenderName());
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
                'john@smith.tld',
                'monique@en-marche-dev.fr',
            ],
            $message->getCC()
        );
    }

    public function testCreateProxyFoundMessage()
    {
        $url = 'https://en-marche.fr/procuration/3/2839f66263bca70ff077d8e47fbdf783';

        $this
            ->urlGenerator
            ->expects($this->once())
            ->method('generate')
            ->willReturn($url)
        ;

        $message = $this->factory->createProxyFoundMessage(
            $this->createAdherentMock('john@smith.tld'),
            $this->createProcurationRequestMock('Marie Bénédicte', 'Dumont', 'marieb.dumont@gmail.tld'),
            $this->createProcurationProxyMock('Monique', 'Clairefontaine', 'monique@en-marche-dev.fr')
        );

        $this->assertInstanceOf(ProcurationProxyFoundMessage::class, $message);
        $this->assertSame('procurations@en-marche-dev.fr', $message->getReplyTo());
        $this->assertSame('Votre mandataire', $message->getSubject());
        $this->assertSame('marieb.dumont@gmail.tld', $message->getRecipient(0)->getEmailAddress());
        $this->assertSame('Marie Bénédicte Dumont', $message->getRecipient(0)->getFullName());
        $this->assertSame('Procuration Macron', $message->getSenderName());
        $this->assertSame(
            [
                'target_firstname' => 'Marie Bénédicte',
                'voter_first_name' => 'Monique',
                'voter_last_name' => 'Clairefontaine',
                'info_link' => $url,
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

    private function createProcurationRequestMock(string $firstNames, string $lastName, string $email)
    {
        $request = $this->createMock(ProcurationRequest::class);
        $request->expects($this->any())->method('getFirstNames')->willReturn($firstNames);
        $request->expects($this->any())->method('getLastName')->willReturn($lastName);
        $request->expects($this->any())->method('getEmailAddress')->willReturn($email);

        return $request;
    }

    private function createProcurationProxyMock(string $firstNames, string $lastName, string $email)
    {
        $proxy = $this->createMock(ProcurationProxy::class);
        $proxy->expects($this->any())->method('getFirstNames')->willReturn($firstNames);
        $proxy->expects($this->any())->method('getLastName')->willReturn($lastName);
        $proxy->expects($this->any())->method('getEmailAddress')->willReturn($email);

        return $proxy;
    }

    private function createAdherentMock(string $email)
    {
        $adherent = $this->createMock(Adherent::class);
        $adherent->expects($this->any())->method('getEmailAddress')->willReturn($email);

        return $adherent;
    }

    protected function setUp()
    {
        parent::setUp();

        $this->urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $this->factory = new ProcurationProxyMessageFactory($this->urlGenerator, 'procurations@en-marche-dev.fr');
    }

    protected function tearDown()
    {
        $this->urlGenerator = null;
        $this->factory = null;

        parent::tearDown();
    }
}
