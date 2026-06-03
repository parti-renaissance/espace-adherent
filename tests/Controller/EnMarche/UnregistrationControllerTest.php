<?php

declare(strict_types=1);

namespace Tests\App\Controller\EnMarche;

use App\Adherent\Command\RemoveAdherentAndRelatedDataCommand;
use App\Adherent\Handler\RemoveAdherentAndRelatedDataCommandHandler;
use App\Entity\Adherent;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\HttpFoundation\Response;
use Tests\App\AbstractRenaissanceWebTestCase;
use Tests\App\Controller\ControllerTestTrait;

#[Group('functional')]
#[Group('controller')]
class UnregistrationControllerTest extends AbstractRenaissanceWebTestCase
{
    use ControllerTestTrait;

    public function testAdherentCanUnregisterSuccessfully(): void
    {
        $countForbidden = 0;

        /** @var RemoveAdherentAndRelatedDataCommandHandler $handler */
        $handler = $this->client->getContainer()->get('test.'.RemoveAdherentAndRelatedDataCommandHandler::class);

        foreach ($this->getAdherentRepository()->findBy(['source' => null]) as $adherent) {
            $this->getEntityManager(Adherent::class)->detach($adherent);

            $this->authenticateAsAdherent($this->client, $adherent->getEmailAddress());

            $crawler = $this->client->request('GET', '/parametres/mon-compte/desadherer');

            if (Response::HTTP_FORBIDDEN === $this->client->getResponse()->getStatusCode()) {
                ++$countForbidden;
                continue;
            }

            $this->isSuccessful($this->client->getResponse());

            // On the renaissance app (user vox host) the terminate form carries neither
            // a "reasons" nor a "comment" field, so it is submitted as-is.
            $crawler = $this->client->submit(
                $crawler->selectButton('Je confirme la suppression de mon')->form()
            );

            $this->assertStatusCode(Response::HTTP_OK, $this->client);

            self::assertCount(0, $crawler->filter('.form__errors > li'));
            self::assertStringContainsString(
                'Votre adhésion et votre compte Renaissance ont bien été supprimés',
                $crawler->html()
            );

            $handler(new RemoveAdherentAndRelatedDataCommand($adherent->getUuid()));

            $this->client->getCookieJar()->clear();
        }

        self::assertSame(8, $countForbidden);
    }
}
