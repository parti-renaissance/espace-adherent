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
 * The action creation mail targets the commune-level audience: adherents + members (sympathisant:membre)
 * subscribed to event_email; other sympathisants (e.g. adhesion_incomplete) are excluded.
 * For Paris/Lyon/Marseille the commune-level zone is the arrondissement (borough).
 * Asserted on commune 77288 and Paris arrondissement 75108 from the shared LoadAdherentData set.
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

    // Adherent of Paris arrondissement 75108 (zone_borough_75108), subscribed to event_email.
    private const BOROUGH_ADHERENT_IN = 'jacques.picard@en-marche.fr';

    /** @var Action[] */
    private array $actions = [];

    protected function tearDown(): void
    {
        foreach ($this->actions as $action) {
            if ($managed = $this->manager->find(Action::class, $action->getId())) {
                $this->manager->remove($managed);
            }
        }
        $this->manager->flush();

        $this->actions = [];

        parent::tearDown();
    }

    public function testActionCreationMailTargetsCommuneMembersAndAdherentsWithEventPreference(): void
    {
        $action = $this->persistAction($this->zone('77288', Zone::CITY), '77000-77288');

        $handler = $this->get(SendActionCreationNotificationCommandHandler::class);
        $handler(new SendActionCreationNotificationCommand($action->getUuid()));

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

    public function testActionCreationMailReachesArrondissementAudience(): void
    {
        $action = $this->persistAction($this->zone('75108', Zone::BOROUGH), '75008-75108');

        $handler = $this->get(SendActionCreationNotificationCommandHandler::class);
        $handler(new SendActionCreationNotificationCommand($action->getUuid()));

        $repository = $this->getEmailRepository();
        $received = static fn (string $email): bool => [] !== $repository->findRecipientMessages(ActionNotificationMessage::class, $email);

        // An action attached to a Paris arrondissement (borough) reaches that arrondissement's adherents.
        self::assertTrue($received(self::BOROUGH_ADHERENT_IN));
        // Adherents of other communes are not reached.
        self::assertFalse($received(self::ADHERENT_OTHER_ZONE));
    }

    private function persistAction(Zone $zone, string $address): Action
    {
        $action = new Action();
        $action->type = 'pap';
        $action->date = new \DateTime('+2 days');
        $action->description = '<p>Action de terrain</p>';
        $action->setPostAddress($this->createPostAddress('2 avenue Jean Jaurès', $address));
        $action->setAuthor($this->getAdherent(LoadAdherentData::ADHERENT_2_UUID));
        $action->addZone($zone);

        $this->manager->persist($action);
        $this->manager->flush();

        $this->actions[] = $action;

        return $action;
    }

    private function zone(string $code, string $type): Zone
    {
        return $this->getRepository(Zone::class)->findOneBy(['code' => $code, 'type' => $type]);
    }
}
