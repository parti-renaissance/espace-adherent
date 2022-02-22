<?php

namespace Tests\App\Security;

use App\Adherent\LastLoginGroupEnum;
use App\Entity\Adherent;
use App\Entity\PostAddress;
use App\Membership\ActivityPositionsEnum;
use App\Security\AdherentLoginGroupRecorder;
use Doctrine\ORM\EntityManagerInterface as ObjectManager;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Security\Guard\Token\PostAuthenticationGuardToken;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

class AdherentLoginGroupRecorderTest extends TestCase
{
    public function testRecordLoginGroup(): void
    {
        $manager = $this->getMockBuilder(ObjectManager::class)->getMock();
        $manager->expects($this->once())->method('flush');

        $bus = $this->createConfiguredMock(MessageBusInterface::class, [
            'dispatch' => new Envelope(new \stdClass()),
        ]);

        $adherent = $this->createAdherent();

        $request = Request::create('POST', '/connexion');
        $token = new PostAuthenticationGuardToken($adherent, 'main', $adherent->getRoles());

        $recorder = new AdherentLoginGroupRecorder($manager, $bus);

        $this->assertNull($adherent->getLastLoginGroup());

        $recorder->onSecurityInteractiveLogin(new InteractiveLoginEvent($request, $token));

        $this->assertNotNull($adherent->getLastLoginGroup());
        $this->assertEquals(LastLoginGroupEnum::LESS_THAN_1_MONTH, $adherent->getLastLoginGroup());
    }

    private function createAdherent(): Adherent
    {
        return Adherent::create(
            Adherent::createUuid('john.smith@example.org'),
            'john.smith@example.org',
            'super-password',
            'male',
            'John',
            'Smith',
            new \DateTime('1990-12-12'),
            ActivityPositionsEnum::RETIRED,
            PostAddress::createForeignAddress('CH', '8002', 'Zürich', 'Brandschenkestrasse')
        );
    }
}
