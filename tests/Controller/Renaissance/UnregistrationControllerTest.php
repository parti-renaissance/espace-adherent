<?php

namespace Tests\App\Controller\Renaissance;

use App\Adherent\Command\RemoveAdherentAndRelatedDataCommand;
use App\Adherent\Handler\RemoveAdherentAndRelatedDataCommandHandler;
use App\Entity\Adherent;
use App\Membership\MembershipSourceEnum;
use Symfony\Component\HttpFoundation\Response;
use Tests\App\AbstractWebCaseTest as WebTestCase;
use Tests\App\Controller\ControllerTestTrait;

/**
 * @group functional
 * @group controller
 */
class UnregistrationControllerTest extends WebTestCase
{
    use ControllerTestTrait;

    public function testAdherentCanUnregisterSuccessfully(): void
    {
        $countForbidden = 0;

        /** @var RemoveAdherentAndRelatedDataCommandHandler $handler */
        $handler = $this->client->getContainer()->get('test.'.RemoveAdherentAndRelatedDataCommandHandler::class);

        foreach ($this->getAdherentRepository()->findBy(['source' => MembershipSourceEnum::RENAISSANCE]) as $adherent) {
            $this->getEntityManager(Adherent::class)->detach($adherent);

            $this->authenticateAsAdherent($this->client, $email = $adherent->getEmailAddress());

            $crawler = $this->client->request('GET', '/parametres/mon-compte/desadherer');

            if (Response::HTTP_FORBIDDEN === $this->client->getResponse()->getStatusCode()) {
                ++$countForbidden;
                continue;
            }

            $this->isSuccessful($this->client->getResponse());

            $crawler = $this->client->submit($crawler->selectButton('Je confirme la suppression de mon adhésion')->form());

            $this->assertResponseIsSuccessful($email.' fail');

            self::assertCount(0, $crawler->filter('.re-form-error'));
            self::assertStringContainsString(
                'Votre adhésion et votre compte Renaissance ont bien été supprimés, vos données personnelles ont été effacées de notre base.',
                $this->client->getResponse()->getContent()
            );

            $handler(new RemoveAdherentAndRelatedDataCommand($adherent->getUuid()));
        }

        self::assertSame(2, $countForbidden);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->client->setServerParameter('HTTP_HOST', self::$container->getParameter('renaissance_host'));
    }
}
