<?php

namespace Tests\App\Security;

use App\Entity\Adherent;
use App\Entity\PostAddress;
use App\Membership\ActivityPositionsEnum;
use App\Security\AdherentLoginTimestampRecorder;
use Doctrine\ORM\EntityManagerInterface as ObjectManager;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

class AdherentLoginTimestampRecorderTest extends TestCase
{
    public function testRecordLastLoginTimestamp()
    {
        $manager = $this->getMockBuilder(ObjectManager::class)->getMock();
        $manager->expects($this->once())->method('flush');

        $adherent = $this->createAdherent();

        $request = Request::create('POST', '/connexion');
        $token = new UsernamePasswordToken($adherent, 'main', $adherent->getRoles());

        $recorder = new AdherentLoginTimestampRecorder($manager);

        $this->assertNull($adherent->getLastLoggedAt());

        $recorder->onSecurityInteractiveLogin(new InteractiveLoginEvent($request, $token));

        $this->assertInstanceOf(\DateTime::class, $adherent->getLastLoggedAt());
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
