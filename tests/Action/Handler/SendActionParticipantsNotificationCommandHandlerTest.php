<?php

declare(strict_types=1);

namespace Tests\App\Action\Handler;

use App\Action\Command\SendActionParticipantsNotificationCommand;
use App\Action\Handler\SendActionParticipantsNotificationCommandHandler;
use App\DataFixtures\ORM\LoadAdherentData;
use App\Entity\Action\Action;
use App\Entity\Action\ActionParticipant;
use App\Entity\Adherent;
use App\Mailer\Message\Renaissance\ActionCancellationMessage;
use App\Mailer\Message\Renaissance\ActionUpdateMessage;
use PHPUnit\Framework\Attributes\Group;
use Tests\App\AbstractKernelTestCase;

/**
 * The action update/cancel mails target the action participants (no "confirmed" notion on actions).
 * The notification is dispatched asynchronously by ActionMessageNotifierListener and produced by this handler.
 */
#[Group('functional')]
class SendActionParticipantsNotificationCommandHandlerTest extends AbstractKernelTestCase
{
    private ?Action $action = null;
    private ?Adherent $firstParticipant = null;
    private ?Adherent $secondParticipant = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->firstParticipant = $this->getAdherent(LoadAdherentData::ADHERENT_2_UUID);
        $this->secondParticipant = $this->getAdherent(LoadAdherentData::ADHERENT_6_UUID);

        $action = new Action();
        $action->type = 'pap';
        $action->date = new \DateTime('+2 days');
        $action->setPostAddress($this->createPostAddress('68 rue du Rocher', '75008-75108'));
        $action->setAuthor($this->firstParticipant);
        $action->addNewParticipant($this->firstParticipant);
        $action->addNewParticipant($this->secondParticipant);

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

        $this->action = $this->firstParticipant = $this->secondParticipant = null;

        parent::tearDown();
    }

    public function testUpdateMailTargetsAllParticipants(): void
    {
        $handler = $this->get(SendActionParticipantsNotificationCommandHandler::class);
        $handler(new SendActionParticipantsNotificationCommand($this->action->getUuid()));

        $repository = $this->getEmailRepository();
        self::assertNotEmpty($repository->findRecipientMessages(ActionUpdateMessage::class, $this->firstParticipant->getEmailAddress()));
        self::assertNotEmpty($repository->findRecipientMessages(ActionUpdateMessage::class, $this->secondParticipant->getEmailAddress()));
    }

    public function testCancellationMailTargetsAllParticipants(): void
    {
        $handler = $this->get(SendActionParticipantsNotificationCommandHandler::class);
        $handler(new SendActionParticipantsNotificationCommand($this->action->getUuid(), true));

        $repository = $this->getEmailRepository();
        self::assertNotEmpty($repository->findRecipientMessages(ActionCancellationMessage::class, $this->firstParticipant->getEmailAddress()));
        self::assertNotEmpty($repository->findRecipientMessages(ActionCancellationMessage::class, $this->secondParticipant->getEmailAddress()));
    }
}
