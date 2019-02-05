<?php

namespace Tests\AppBundle\Controller\EnMarche;

use AppBundle\Entity\DeputyManagedUsersMessage;
use AppBundle\Repository\DeputyManagedUsersMessageRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\AppBundle\Controller\ControllerTestTrait;
use Liip\FunctionalTestBundle\Test\WebTestCase;

/**
 * @group functional
 * @group deputy
 */
class DeputyControllerTest extends WebTestCase
{
    use ControllerTestTrait;

    /**
     * @var DeputyManagedUsersMessageRepository
     */
    private $deputyMessageRepository;

    public function testSendMailSuccessful()
    {
        $deputyEmail = 'deputy@en-marche-dev.fr';
        $this->authenticateAsAdherent($this->client, $deputyEmail);

        $this->client->request(Request::METHOD_GET, '/espace-depute/utilisateurs/message');
        $subject = 'Message from your deputy';
        $content = 'Content of a deputy message.';
        $data = [];
        $data['deputy_message']['subject'] = $subject;
        $data['deputy_message']['content'] = $content;
        $this->client->submit($this->client->getCrawler()->selectButton('Envoyer le message')->form(), $data);

        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());
        $this->client->followRedirect();

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertContains('http://'.$this->hosts['app'].'/espace-depute/utilisateurs/message', $this->client->getRequest()->getUri());

        $deputyMessages = $this
            ->deputyMessageRepository
            ->createQueryBuilder('m')
            ->innerJoin('m.from', 'a')
            ->addSelect('a')
            ->getQuery()
            ->getResult()
        ;

        $this->assertCount(1, $deputyMessages);

        /* @var DeputyManagedUsersMessage */
        $message = reset($deputyMessages);
        $deputy = $this->getAdherentRepository()->findOneByEmail($deputyEmail);

        $this->assertSame($deputyEmail, $message->getFrom()->getEmailAddress());
        $this->assertSame($subject, $message->getSubject());
        $this->assertSame($content, $message->getContent());
        $this->assertSame($deputy->getManagedDistrict()->getId(), $message->getDistrict()->getId());
        $this->assertSame(0, $message->getOffset());
    }

    protected function setUp()
    {
        parent::setUp();

        $this->init();

        $this->deputyMessageRepository = $this->manager->getRepository(DeputyManagedUsersMessage::class);
    }

    protected function tearDown()
    {
        $this->kill();

        $this->deputyMessageRepository = null;

        parent::tearDown();
    }
}
