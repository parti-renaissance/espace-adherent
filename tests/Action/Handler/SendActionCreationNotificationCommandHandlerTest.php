<?php

declare(strict_types=1);

namespace Tests\App\Action\Handler;

use App\Action\Command\SendActionCreationNotificationCommand;
use App\Action\Handler\SendActionCreationNotificationCommandHandler;
use App\DataFixtures\ORM\LoadAdherentData;
use App\Entity\Action\Action;
use App\Entity\Geo\Zone;
use App\Mailer\Message\Renaissance\ActionNotificationMessage;
use PHPUnit\Framework\Attributes\Group;
use Tests\App\AbstractKernelTestCase;

/**
 * The action creation mail targets the commune (city zone) audience: adherents + members (sympathisant:membre)
 * subscribed to event_email; other sympathisants (e.g. adhesion_incomplete) are excluded.
 * Asserted on commune 77288 from the shared LoadAdherentData set.
 */
#[Group('functional')]
class SendActionCreationNotificationCommandHandlerTest extends AbstractKernelTestCase
{
    private const ADHERENT_IN = 'francis.brioul@yahoo.com';
    private const MEMBER_IN = 'commune-member-1@en-marche-dev.fr';
    private const ADHESION_INCOMPLETE_OUT = 'coalitions-user-1@en-marche-dev.fr';
    private const ADHERENT_NO_SUB = 'je-mengage-user-1@en-marche-dev.fr';
    private const USER_IN = 'adherent-male-a@en-marche-dev.fr';
    private const ADHERENT_OTHER_ZONE = 'laura@deloche.com';

    private ?Action $action = null;

    protected function setUp(): void
    {
        parent::setUp();

        $commune = $this->getRepository(Zone::class)->findOneBy(['code' => '77288', 'type' => Zone::CITY]);
        $author = $this->getAdherent(LoadAdherentData::ADHERENT_2_UUID);

        $action = new Action();
        $action->type = 'pap';
        $action->date = new \DateTime('+2 days');
        $action->description = '<p>Action de terrain</p>';
        $action->setPostAddress($this->createPostAddress('2 avenue Jean Jaurès', '77000-77288'));
        $action->setAuthor($author);
        $action->addZone($commune);

        $this->manager->persist($action);
        $this->manager->flush();

        $this->action = $action;
    }

    protected function tearDown(): void
    {
        if (null !== $this->action && $managed = $this->manager->find(Action::class, $this->action->getId())) {
            $this->manager->remove($managed);
            $this->manager->flush();
        }

        $this->action = null;

        parent::tearDown();
    }

    public function testActionCreationMailTargetsCommuneMembersAndAdherentsWithEventPreference(): void
    {
        $handler = $this->get(SendActionCreationNotificationCommandHandler::class);
        $handler(new SendActionCreationNotificationCommand($this->action->getUuid()));

        $repository = $this->getEmailRepository();
        $received = static fn (string $email): bool => [] !== $repository->findRecipientMessages(ActionNotificationMessage::class, $email);

        // Included: adherent and member (sympathisant:membre) of the commune, subscribed to event_email.
        self::assertTrue($received(self::ADHERENT_IN));
        self::assertTrue($received(self::MEMBER_IN));

        // Excluded: incomplete-adhesion sympathisant (not sympathisant:membre), despite being subscribed.
        self::assertFalse($received(self::ADHESION_INCOMPLETE_OUT));
        // Excluded by preference (no event_email), tag (plain user), and zone (other commune).
        self::assertFalse($received(self::ADHERENT_NO_SUB));
        self::assertFalse($received(self::USER_IN));
        self::assertFalse($received(self::ADHERENT_OTHER_ZONE));
    }
}
