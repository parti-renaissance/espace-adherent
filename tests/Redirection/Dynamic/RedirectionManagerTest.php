<?php

namespace Tests\AppBundle\Redirection\Dynamic;

use AppBundle\Entity\Redirection;
use AppBundle\Redirection\Dynamic\RedirectionManager;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Tests\AppBundle\Controller\ControllerTestTrait;

/**
 * @group functional
 */
class RedirectionManagerTest extends WebTestCase
{
    use ControllerTestTrait;

    public function testSimpleResolveRedirection(): void
    {
        $redirectionManager = $this->getContainer()->get(RedirectionManager::class);

        self::assertNull($redirectionManager->getRedirection('/test1'));
        $redirectionManager->optimiseRedirection($redirectionManager->setRedirection('/test1', '/test2'));
        self::assertNotNull($redirection = $redirectionManager->getRedirection('/test1'));
        self::assertRedirectionValues($redirection, '/test1', '/test2');
    }

    public function testMultipleResolveRedirection(): void
    {
        $redirectionManager = $this->getContainer()->get(RedirectionManager::class);

        self::assertNull($redirectionManager->getRedirection('/test1'));
        $redirectionManager->optimiseRedirection($redirectionManager->setRedirection('/test1', '/test2'));
        $redirectionManager->optimiseRedirection($redirectionManager->setRedirection('/test2', '/test3'));
        $redirectionManager->optimiseRedirection($redirectionManager->setRedirection('/test3', '/test4'));
        self::assertNotNull($redirection = $redirectionManager->getRedirection('/test1'));
        self::assertRedirectionValues($redirection, '/test1', '/test4');
        self::assertNotNull($redirection = $redirectionManager->getRedirection('/test2'));
        self::assertRedirectionValues($redirection, '/test2', '/test4');
        self::assertNotNull($redirection = $redirectionManager->getRedirection('/test3'));
        self::assertRedirectionValues($redirection, '/test3', '/test4');
    }

    public function testLoopRedirection(): void
    {
        $redirectionManager = $this->getContainer()->get(RedirectionManager::class);

        self::assertNull($redirectionManager->getRedirection('/test1'));
        $redirectionManager->optimiseRedirection($redirectionManager->setRedirection('/test1', '/test2'));
        $redirectionManager->optimiseRedirection($redirectionManager->setRedirection('/test2', '/test3'));
        $redirectionManager->optimiseRedirection($redirectionManager->setRedirection('/test3', '/test4'));
        $redirectionManager->optimiseRedirection($redirectionManager->setRedirection('/test4', '/test1'));
        self::assertNull($redirectionManager->getRedirection('/test1'));
        self::assertNotNull($redirection = $redirectionManager->getRedirection('/test2'));
        self::assertRedirectionValues($redirection, '/test2', '/test1');
        self::assertNotNull($redirection = $redirectionManager->getRedirection('/test3'));
        self::assertRedirectionValues($redirection, '/test3', '/test1');
        self::assertNotNull($redirection = $redirectionManager->getRedirection('/test4'));
        self::assertRedirectionValues($redirection, '/test4', '/test1');
    }

    private static function assertRedirectionValues(Redirection $redirection, string $source, string $target, int $type = 301): void
    {
        self::assertSame($source, $redirection->getFrom());
        self::assertSame($target, $redirection->getTo());
        self::assertSame($type, $redirection->getType());
    }

    protected function setUp()
    {
        $this->getContainer()->get('app.cache.redirection')->clear();
    }

    protected function tearDown()
    {
        $this->kill();

        parent::tearDown();
    }
}
