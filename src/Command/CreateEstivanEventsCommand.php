<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Action\Action;
use App\Entity\Event\Event;
use App\Entity\Event\EventCategory;
use App\Entity\NullablePostAddress;
use App\Entity\PostAddress;
use App\Event\EventRegistrationCommand;
use App\Event\EventRegistrationCommandHandler;
use App\Scope\Generator\JemScopeGenerator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * One-shot: converts the EstiVan {@see Action} rows into published {@see Event} rows.
 *
 * This bypasses the POST /v3/events API but reproduces its DATA side-effects while suppressing every
 * notification, by design (strategy "no dispatch"):
 *   - Kept   : Algolia + timeline mirror/indexer (triggered on persist via the entity listener),
 *              PrePersist defaults, and the organizer auto-registration + participant counters
 *              (replayed by calling EventRegistrationCommandHandler with sendMail=false).
 *   - Dropped: creation push + creation/registration e-mails. The Symfony `EVENT_CREATED` event is
 *              never dispatched, so SendEventPushNotificationListener and EventMessageNotifierListener
 *              can never fire — notifications are impossible by construction, not by a toggle.
 *
 * Field mapping (Action -> Event): name = "EstiVan - <city>" (city from the Action address),
 * description = Action.description, beginAt = Action.date, finishAt = same day 17:00, category = the
 * "estivan" slug, mode = meeting, visibility = public, published = true, committee = null. Zones and the
 * already-geocoded address are copied from the Action (no geocoder call). The image already exists on the
 * bucket; only its stored filename is written to the column.
 *
 * Intended to run inside a prod pod (`bin/console app:estivan:create-events`), where RabbitMQ + workers
 * index normally. Dry-run by default; pass --force to write.
 */
#[AsCommand(
    name: 'app:estivan:create-events',
    description: 'One-shot: convert the EstiVan actions into published events (indexing + organizer registration, no notifications).',
)]
class CreateEstivanEventsCommand extends Command
{
    private const int AUTHOR_ID = 2259189;
    private const string DESCRIPTION_PATTERN = '%estivan%';
    private const int EXPECTED_COUNT = 21;
    private const string CATEGORY_SLUG = 'estivan';
    private const string IMAGE_NAME = 'b438a0ae1486b9208fd4297ae92e0b78.webp';
    private const string NAME_PREFIX = 'EstiVan - ';
    private const int FINISH_HOUR = 17;

    private SymfonyStyle $io;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly EventRegistrationCommandHandler $registrationHandler,
        private readonly JemScopeGenerator $jemScopeGenerator,
        private readonly ValidatorInterface $validator,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('force', null, InputOption::VALUE_NONE, 'Actually create the events. Without it the command only previews (dry-run).');
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $force = (bool) $input->getOption('force');

        $category = $this->entityManager->getRepository(EventCategory::class)->findOneBy(['slug' => self::CATEGORY_SLUG]);
        if (!$category instanceof EventCategory) {
            $this->io->error(\sprintf('Event category with slug "%s" not found.', self::CATEGORY_SLUG));

            return self::FAILURE;
        }

        $actions = $this->fetchActions();
        $count = \count($actions);

        if (self::EXPECTED_COUNT !== $count) {
            $this->io->error(\sprintf(
                'Expected %d actions but found %d (author_id=%d, description LIKE "%s"). Aborting — verify the selection.',
                self::EXPECTED_COUNT,
                $count,
                self::AUTHOR_ID,
                self::DESCRIPTION_PATTERN,
            ));

            return self::FAILURE;
        }

        $rows = [];
        $blocking = 0;
        foreach ($actions as $action) {
            [$event, $problems, $exists] = $this->buildEvent($action, $category);

            if ($problems && !$exists) {
                ++$blocking;
            }

            $rows[] = [
                $action->getUuid()->toString(),
                $event->getName(),
                $event->getBeginAt()?->format('Y-m-d H:i'),
                $event->getFinishAt()?->format('H:i'),
                $event->getPostAddress()?->getCityName(),
                $exists ? 'SKIP (exists)' : ($problems ? 'BLOCKED: '.implode('; ', $problems) : 'OK'),
            ];
        }

        $this->io->table(['Action UUID', 'Name', 'BeginAt', 'FinishAt', 'City', 'Status'], $rows);

        if ($blocking > 0) {
            $this->io->error(\sprintf('%d action(s) have blocking problems. Fix the source data before running with --force.', $blocking));

            return self::FAILURE;
        }

        if (!$force) {
            $this->io->note('Dry-run: nothing written. Re-run with --force to create the events.');

            return self::SUCCESS;
        }

        if (!$this->io->confirm(\sprintf('Create %d events in the CURRENT database (writes to prod when run in a prod pod)?', $count), false)) {
            return self::FAILURE;
        }

        $created = 0;
        $skipped = 0;
        foreach ($actions as $action) {
            [$event, , $exists] = $this->buildEvent($action, $category);

            if ($exists) {
                ++$skipped;
                $this->io->writeln(\sprintf(' <comment>~</comment> %s already exists, skipped', $event->getName()));

                continue;
            }

            // Persist + flush triggers PrePersist defaults + Algolia/timeline indexing (entity listener).
            $this->entityManager->persist($event);
            $this->entityManager->flush();

            // Replays AddFirstEventRegistrationSubscriber: registers the organizer and updates the member
            // counters. sendMail=false suppresses the confirmation e-mail; no referrer => no private message.
            if ($author = $event->getAuthor()) {
                $this->registrationHandler->handle(new EventRegistrationCommand($event, $author), false);
            }

            ++$created;
            $this->io->writeln(\sprintf(' <info>✓</info> %s (%s)', $event->getName(), $event->getUuid()->toString()));
        }

        $this->io->success(\sprintf('%d event(s) created, %d skipped (already existed).', $created, $skipped));

        return self::SUCCESS;
    }

    /**
     * @return list<Action>
     */
    private function fetchActions(): array
    {
        return $this->entityManager->createQueryBuilder()
            ->select('a')
            ->from(Action::class, 'a')
            ->where('a.description LIKE :pattern')
            ->andWhere('IDENTITY(a.author) = :authorId')
            ->orderBy('a.date', 'ASC')
            ->setParameter('pattern', self::DESCRIPTION_PATTERN)
            ->setParameter('authorId', self::AUTHOR_ID)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return array{0: Event, 1: list<string>, 2: bool} [event, blocking problems, already exists]
     */
    private function buildEvent(Action $action, EventCategory $category): array
    {
        $problems = [];
        $address = $action->getPostAddress();
        $city = $address->getCityName();

        $event = new Event();
        $event->setName(self::NAME_PREFIX.($city ?? ''));
        $event->setCategory($category);
        $event->setMode(Event::MODE_MEETING);
        // Default sendInvitationEmail is true; EVENT_CREATED is never dispatched anyway, this is belt-and-braces.
        $event->sendInvitationEmail = false;

        if (null !== $action->description) {
            $event->setDescription($action->description);
        } else {
            $problems[] = 'action has no description';
        }

        if (null === $city) {
            $problems[] = 'action has no city name';
        }

        if ($action->date instanceof \DateTime) {
            $beginAt = clone $action->date;
            $finishAt = (clone $beginAt)->setTime(self::FINISH_HOUR, 0);
            $event->setBeginAt($beginAt);
            $event->setFinishAt($finishAt);

            if ($finishAt <= $beginAt) {
                $problems[] = \sprintf('finishAt (%s) <= beginAt (%s)', $finishAt->format('H:i'), $beginAt->format('H:i'));
            }
        } else {
            $problems[] = 'action has no date';
        }

        // Author instance = the author's JEM scope, populated the same way the API does (updateFromScope,
        // which also sets instanceKey). The JEM scope carries no zone-based role here, so the author zone
        // is empty.
        if ($author = $action->getAuthor()) {
            $event->updateFromScope($this->jemScopeGenerator->generate($author));
        }

        // Zones are already computed on the Action: copy them instead of recomputing.
        foreach ($action->getZones() as $zone) {
            $event->addZone($zone);
        }

        // Address already geocoded on the Action: copy it with coordinates so no geocoding runs.
        $event->setPostAddress($this->copyAddress($address));

        // The image file already exists on the bucket; write its stored filename to the column.
        $this->setStoredImageName($event, self::IMAGE_NAME);

        $exists = $this->eventAlreadyExists($event);

        if (!$exists) {
            foreach ($this->validator->validate($event) as $violation) {
                $problems[] = \sprintf('%s: %s', $violation->getPropertyPath(), $violation->getMessage());
            }
        }

        return [$event, $problems, $exists];
    }

    private function copyAddress(PostAddress $source): NullablePostAddress
    {
        $address = NullablePostAddress::createAddress(
            $source->getCountry(),
            $source->getPostalCode(),
            $source->getCityName(),
            $source->getAddress(),
            $source->getAdditionalAddress(),
            $source->getRegion(),
            $source->getLatitude(),
            $source->getLongitude(),
        );
        $address->setCity($source->getCity());

        if ($hash = $source->getGeocodableHash()) {
            $address->setGeocodableHash($hash);
        }

        return $address;
    }

    /**
     * `imageName` is a protected column and the only public setter expects an UploadedFile (it recomputes a
     * md5 filename). The file already lives on the bucket under this exact name, so we set the stored value
     * directly, before persist, so the Algolia/timeline indexing picks up the image. Scoped to a one-shot.
     */
    private function setStoredImageName(Event $event, string $imageName): void
    {
        (function () use ($imageName): void {
            $this->imageName = $imageName;
        })->call($event);
    }

    private function eventAlreadyExists(Event $event): bool
    {
        if (!$event->getBeginAt()) {
            return false;
        }

        return null !== $this->entityManager->getRepository(Event::class)->findOneBy([
            'canonicalName' => $event->getCanonicalName(),
            'beginAt' => $event->getBeginAt(),
        ]);
    }
}
