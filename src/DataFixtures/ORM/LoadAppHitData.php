<?php

namespace App\DataFixtures\ORM;

use App\AppSession\SystemEnum;
use App\Entity\Adherent;
use App\Entity\AppHit;
use App\Entity\AppSession;
use App\Entity\Event\Event;
use App\JeMengage\Hit\EventTypeEnum;
use App\JeMengage\Hit\TargetTypeEnum;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class LoadAppHitData extends AbstractLoadPostAddressData implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        $adherentRepo = $manager->getRepository(Adherent::class);
        $eventRepo = $manager->getRepository(Event::class);
        $sessionRepo = $manager->getRepository(AppSession::class);

        /** @var AppSession[] $sessions */
        $sessions = $sessionRepo->findAll();

        $sessionsByAdherent = [];
        foreach ($sessions as $sess) {
            $aid = $sess->adherent?->getId();
            if (null !== $aid) {
                $sessionsByAdherent[$aid][] = $sess;
            }
        }

        $pickAppSession = static function (Adherent $adherent, \DateTimeInterface $when) use ($sessionsByAdherent, $faker): ?AppSession {
            $aid = $adherent->getId();
            if (empty($sessionsByAdherent[$aid])) {
                return null;
            }
            $pool = $sessionsByAdherent[$aid];
            /** @var AppSession $sess */
            $sess = $pool[array_rand($pool)];

            $created = $sess->getCreatedAt();
            $diff = $when->diff($created)->days ?: 999;
            if ($diff > 30) {
                return null;
            }

            return $faker->boolean(70) ? $sess : null;
        };

        /** @var Adherent[] $adherents */
        $adherents = $adherentRepo->findAll();

        /** @var Event[] $events */
        $events = $eventRepo->findAll();

        if (!$adherents || !$events) {
            return;
        }

        $systems = [SystemEnum::WEB, SystemEnum::IOS, SystemEnum::ANDROID];

        $sources = [
            null,
            'page_timeline',
            'direct_link',
            'page_events',
            'page_publication_edition',
            'push_notification',
            'reload',
        ];

        $now = new \DateTimeImmutable();

        foreach ($events as $event) {
            $copyAdherents = $adherents;
            $eventUuid = (string) $event->getUuid();
            $random = LoadEventData::EVENT_8_UUID !== $eventUuid;

            $sessionsCount = $random ? $faker->numberBetween(8, 18) : 10;

            for ($s = 0; $s < $sessionsCount; ++$s) {
                shuffle($copyAdherents);
                $adherent = array_shift($copyAdherents);
                $maybeRef = $adherents[array_rand($adherents)];
                $referrer = $maybeRef !== $adherent ? $maybeRef : null;

                $activityUuid = Uuid::uuid4();
                $sessionStart = $now
                    ->sub(new \DateInterval('P'.$faker->numberBetween(0, 14).'D'))
                    ->setTime($faker->numberBetween(8, 22), $faker->numberBetween(0, 59))
                ;

                $appSystem = $systems[array_rand($systems)];
                $appVersion = 'v5.15.5#5';

                $appSessionForSessionStart = $pickAppSession($adherent, $sessionStart);

                // 1) activity_session
                $manager->persist(self::makeHit(
                    eventType: EventTypeEnum::ActivitySession,
                    adherent: $adherent,
                    referrer: $referrer,
                    objectType: null,
                    objectId: null,
                    source: 'page_events',
                    activitySessionUuid: $activityUuid,
                    appSystem: $appSystem,
                    appVersion: $appVersion,
                    appDate: $sessionStart,
                    targetUrl: null,
                    buttonName: null,
                    faker: $faker,
                    appSession: $appSessionForSessionStart,
                ));

                // 2) impression (sur la page événements, puis parfois timeline)
                $impressions = $random ? $faker->numberBetween(1, 3) : 2;
                for ($i = 0; $i < $impressions; ++$i) {
                    $when = $sessionStart->add(new \DateInterval('PT'.($i * $faker->numberBetween(5, 60)).'M'));
                    $manager->persist(self::makeHit(
                        eventType: EventTypeEnum::Impression,
                        adherent: $adherent,
                        referrer: $referrer,
                        objectType: TargetTypeEnum::Event,
                        objectId: $eventUuid,
                        source: 0 === $i || !$random ? 'page_events' : $faker->randomElement(['page_timeline', 'reload']),
                        activitySessionUuid: $activityUuid,
                        appSystem: $appSystem,
                        appVersion: $appVersion,
                        appDate: $when,
                        targetUrl: '/evenements/'.$eventUuid,
                        buttonName: null,
                        faker: $faker,
                        appSession: $appSessionForSessionStart,
                    ));
                }

                // 3) open
                if (!$random || $faker->boolean(70)) {
                    $when = $sessionStart->add(new \DateInterval('PT'.$faker->numberBetween(1, 90).'M'));
                    $manager->persist(self::makeHit(
                        eventType: EventTypeEnum::Open,
                        adherent: $adherent,
                        referrer: $referrer,
                        objectType: TargetTypeEnum::Event,
                        objectId: $eventUuid,
                        source: $random ? $faker->randomElement(['push_notification', 'direct_link']) : 'direct_link',
                        activitySessionUuid: $activityUuid,
                        appSystem: $appSystem,
                        appVersion: $appVersion,
                        appDate: $when,
                        targetUrl: '/evenements/'.$eventUuid.'?utm_source=push',
                        buttonName: null,
                        faker: $faker,
                        appSession: $appSessionForSessionStart,
                    ));
                }

                // 4) click
                if (!$random || $faker->boolean(55)) {
                    $when = $sessionStart->add(new \DateInterval('PT'.$faker->numberBetween(2, 120).'M'));
                    $manager->persist(self::makeHit(
                        eventType: EventTypeEnum::Click,
                        adherent: $adherent,
                        referrer: $referrer,
                        objectType: TargetTypeEnum::Event,
                        objectId: $eventUuid,
                        source: $random ? $faker->randomElement($sources) : 'page_events',
                        activitySessionUuid: $activityUuid,
                        appSystem: $appSystem,
                        appVersion: $appVersion,
                        appDate: $when,
                        targetUrl: '/evenements/'.$eventUuid.'/inscription',
                        buttonName: $faker->randomElement(['cta_register', 'cta_share', 'cta_remind_me']),
                        faker: $faker,
                        appSession: $appSessionForSessionStart,
                    ));
                }

                // publication/news/action
                if ($faker->boolean(30)) {
                    /** @var TargetTypeEnum $otherType */
                    $otherType = $faker->randomElement([TargetTypeEnum::Publication, TargetTypeEnum::News, TargetTypeEnum::Action, TargetTypeEnum::Alert]);
                    $when = $sessionStart->add(new \DateInterval('PT'.$faker->numberBetween(1, 50).'M'));
                    $manager->persist(self::makeHit(
                        eventType: EventTypeEnum::Impression,
                        adherent: $adherent,
                        referrer: $referrer,
                        objectType: $otherType,
                        objectId: (string) Uuid::uuid4(),
                        source: 'page_publication_edition',
                        activitySessionUuid: $activityUuid,
                        appSystem: $appSystem,
                        appVersion: $appVersion,
                        appDate: $when,
                        targetUrl: '/'.$otherType->value.'/'.$faker->slug(),
                        buttonName: null,
                        faker: $faker,
                        appSession: $appSessionForSessionStart,
                    ));
                }
            }
        }

        $manager->flush();
    }

    /**
     * Fabrique un AppHit correctement rempli
     */
    private static function makeHit(
        EventTypeEnum $eventType,
        Adherent $adherent,
        ?Adherent $referrer,
        ?TargetTypeEnum $objectType,
        ?string $objectId,
        ?string $source,
        UuidInterface $activitySessionUuid,
        ?SystemEnum $appSystem,
        string $appVersion,
        \DateTimeInterface $appDate,
        ?string $targetUrl,
        ?string $buttonName,
        Generator $faker,
        ?AppSession $appSession = null,
    ): AppHit {
        $hit = new AppHit();
        $hit->eventType = $eventType;
        $hit->adherent = $adherent;
        $hit->referrer = $referrer;
        $hit->appSession = $appSession;
        $hit->referrerCode = $referrer ? $faker->bothify('REF-###??') : null;

        $hit->activitySessionUuid = $activitySessionUuid;
        $hit->openType = null;

        $hit->objectType = $objectType;
        $hit->objectId = $objectId;

        $hit->source = $source;
        $hit->buttonName = $buttonName;
        $hit->targetUrl = $targetUrl;

        $hit->userAgent = $faker->userAgent();

        $hit->appSystem = $appSystem;
        $hit->appVersion = $appVersion;
        $hit->appDate = $appDate;

        $hit->raw = [
            'ip' => $faker->ipv4(),
            'lang' => $faker->randomElement(['fr-FR', 'fr', 'en-US']),
            'screen' => [$faker->numberBetween(360, 1920), $faker->numberBetween(640, 1200)],
            'headers' => [
                'dnt' => (string) (int) $faker->boolean(),
                'accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            ],
        ];

        return $hit;
    }

    public function getDependencies(): array
    {
        return [
            LoadAdherentData::class,
            LoadEventData::class,
            LoadAppSessionData::class,
        ];
    }
}
