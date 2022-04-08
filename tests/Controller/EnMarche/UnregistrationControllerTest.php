<?php

namespace Tests\App\Controller\EnMarche;

use App\Adherent\Command\RemoveAdherentAndRelatedDataCommand;
use App\Adherent\Handler\RemoveAdherentAndRelatedDataCommandHandler;
use App\Entity\Adherent;
use App\Entity\Unregistration;
use App\SendInBlue\Client;
use Symfony\Component\HttpFoundation\Response;
use Tests\App\AbstractWebCaseTest as WebTestCase;
use Tests\App\Controller\ControllerTestTrait;
use Tests\App\Test\SendInBlue\DummyClient;

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

        foreach ($this->getAdherentRepository()->findAll() as $adherent) {
            $this->getEntityManager(Adherent::class)->detach($adherent);

            $this->authenticateAsAdherent($this->client, $email = $adherent->getEmailAddress());

            $crawler = $this->client->request('GET', '/parametres/mon-compte/desadherer');

            if (Response::HTTP_FORBIDDEN === $this->client->getResponse()->getStatusCode()) {
                ++$countForbidden;
                continue;
            }

            $this->isSuccessful($this->client->getResponse());

            self::assertEmpty($this->getSendInBlueClient()->getDeleteSchedule());

            $reasons = Unregistration::REASONS_LIST_ADHERENT;
            $reasonsValues = array_values($reasons);
            $chosenReasons = [
                3 => $reasonsValues[3],
                4 => $reasonsValues[4],
            ];

            $crawler = $this->client->submit(
                $crawler->selectButton('Je confirme la suppression de mon')->form(
                    [
                        'unregistration' => [
                            'reasons' => $chosenReasons,
                            'comment' => 'Je me désinscris',
                        ],
                    ]
                )
            );

            $this->assertStatusCode(Response::HTTP_OK, $this->client);

            $sendInBlueDeletes = $this->getSendInBlueClient()->getDeleteSchedule();
            self::assertCount(1, $sendInBlueDeletes);
            self::assertContains($email, $sendInBlueDeletes);

            self::assertCount(0, $crawler->filter('.form__errors > li'));
            self::assertSame(
                $adherent->isUser(
                ) ? 'Votre compte En Marche a bien été supprimé et vos données personnelles effacées de notre base.' :
                    'Votre adhésion et votre compte En Marche ont bien été supprimés et vos données personnelles effacées de notre base.',
                trim($crawler->filter('#is_not_adherent h1')->eq(0)->text())
            );

            $handler(new RemoveAdherentAndRelatedDataCommand($adherent->getUuid()));
        }

        self::assertSame(31, $countForbidden);
    }

    private function getSendInBlueClient(): DummyClient
    {
        return $this->client->getContainer()->get(Client::class);
    }
}
