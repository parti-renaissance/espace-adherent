<?php

declare(strict_types=1);

namespace Tests\App\Action\EventListener;

use App\Action\ActionEvent;
use App\Action\EventListener\ActionMessageNotifierListener;
use App\DataFixtures\ORM\LoadAdherentData;
use App\Entity\Action\Action;
use App\Entity\Action\ActionParticipant;
use App\Repository\Action\ActionRepository;
use PHPUnit\Framework\Attributes\Group;
use Tests\App\AbstractKernelTestCase;

#[Group('functional')]
class ActionAuthorSerializationRegressionTest extends AbstractKernelTestCase
{
    private ?Action $action = null;

    protected function setUp(): void
    {
        parent::setUp();

        $author = $this->getAdherent(LoadAdherentData::ADHERENT_2_UUID);

        $action = new Action();
        $action->type = 'pap';
        $action->date = new \DateTime('+2 days');
        $action->setPostAddress($this->createPostAddress('68 rue du Rocher', '75008-75108'));
        $action->setAuthor($author);
        $action->addNewParticipant($author);

        $this->manager->persist($action);
        $this->manager->flush();

        $this->action = $action;
    }

    protected function tearDown(): void
    {
        if (null !== $this->action && $managed = $this->manager->find(Action::class, $this->action->getId())) {
            foreach ($this->manager->getRepository(ActionParticipant::class)->findBy(['action' => $managed]) as $participant) {
                $this->manager->remove($participant);
            }
            $this->manager->remove($managed);
            $this->manager->flush();
        }

        $this->action = null;

        parent::tearDown();
    }

    public function testUpdateNotificationDoesNotLeaveAuthorAsPartialWithoutUuid(): void
    {
        $uuid = $this->action->getUuid();

        // Evict everything so the author is only a lazy reference, as it would be in a fresh request.
        $this->manager->clear();
        $action = $this->get(ActionRepository::class)->findOneByUuid($uuid);

        // Simulate the update event firing before the response serialization.
        $this->get(ActionMessageNotifierListener::class)->onActionUpdated(new ActionEvent($action->getAuthor(), $action));

        // Generating the author @id reads its uuid; a polluted PARTIAL author makes this throw.
        self::assertNotNull($action->getAuthor()->getUuid());
    }
}
