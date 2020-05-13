<?php

namespace Tests\App\Controller\Admin;

use App\DataFixtures\ORM\LoadIdeaThreadData;
use App\Entity\IdeasWorkshop\Thread;
use App\Repository\ThreadRepository;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\App\Controller\ControllerTestTrait;

/**
 * @group functional
 * @group admin
 */
class AdminThreadControllerTest extends WebTestCase
{
    use ControllerTestTrait;

    /** @var ThreadRepository $threadRepository */
    private $threadRepository;

    public function testDisableAction(): void
    {
        /** @var Thread $thread */
        $thread = $this->threadRepository->findOneByUuid(LoadIdeaThreadData::THREAD_01_UUID);

        $this->assertTrue($thread->isEnabled());

        $this->authenticateAsAdmin($this->client);

        $this->client->request(Request::METHOD_GET, sprintf('/admin/ideasworkshop-thread/%s/disable', $thread->getUuid()));
        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());

        $this->get('doctrine.orm.entity_manager')->clear();

        $thread = $this->threadRepository->findOneByUuid(LoadIdeaThreadData::THREAD_01_UUID, true);

        $this->assertFalse($thread->isEnabled());
    }

    public function testEnableAction(): void
    {
        /** @var Thread $thread */
        $thread = $this->threadRepository->findOneByUuid(LoadIdeaThreadData::THREAD_09_UUID, true);

        $this->assertFalse($thread->isEnabled());

        $this->authenticateAsAdmin($this->client);

        $this->client->request(Request::METHOD_GET, sprintf('/admin/ideasworkshop-thread/%s/enable', $thread->getUuid()));
        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());

        $this->get('doctrine.orm.entity_manager')->clear();

        $thread = $this->threadRepository->findOneByUuid(LoadIdeaThreadData::THREAD_09_UUID);

        $this->assertTrue($thread->isEnabled());
    }

    protected function setUp()
    {
        parent::setUp();

        $this->init();

        $this->threadRepository = $this->getThreadRepository();
    }

    protected function tearDown()
    {
        $this->kill();

        $this->threadRepository = null;

        parent::tearDown();
    }
}
