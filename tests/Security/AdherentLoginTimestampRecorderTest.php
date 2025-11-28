<?php

declare(strict_types=1);

namespace Tests\App\Security;

use App\Entity\Adherent;
use App\Entity\PostAddress;
use App\Membership\ActivityPositionsEnum;
use App\Security\Http\AuthenticationSuccessHandler;
use App\Security\Http\Session\AnonymousFollowerSession;
use Doctrine\ORM\EntityManagerInterface as ObjectManager;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\HttpUtils;

class AdherentLoginTimestampRecorderTest extends TestCase
{
    public function testRecordLastLoginTimestamp()
    {
        $manager = $this->getMockBuilder(ObjectManager::class)->getMock();
        $manager->expects($this->once())->method('flush');

        $adherent = $this->createAdherent();

        $request = Request::create('POST', '/connexion');
        $token = new UsernamePasswordToken($adherent, 'main', $adherent->getRoles());

        $this->assertNull($adherent->getLastLoggedAt());

        $handler = new AuthenticationSuccessHandler($this->getMockBuilder(HttpUtils::class)->getMock());
        $handler->setManager($manager);
        $handler->setAnonymousFollowerSession($this->getMockBuilder(AnonymousFollowerSession::class)->disableOriginalConstructor()->getMock());

        $handler->onAuthenticationSuccess($request, $token);

        $this->assertInstanceOf(\DateTime::class, $adherent->getLastLoggedAt());
    }

    private function createAdherent(): Adherent
    {
        return Adherent::create(
            Adherent::createUuid('john.smith@example.org'),
            'ABC-234',
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
