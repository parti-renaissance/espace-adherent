<?php

namespace App\Normalizer;

use App\Adherent\Tag\TagEnum;
use App\Api\Serializer\EventContextBuilder;
use App\Entity\Adherent;
use App\Entity\Event\BaseEvent;
use App\Event\EventVisibilityEnum;
use App\Repository\EventRegistrationRepository;
use App\Security\Voter\Event\CanManageEventVoter;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class EventNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    public const EVENT_USER_REGISTRATION_ALREADY_CALLED = 'event_normalizer';

    public function __construct(
        private readonly Security $security,
        private readonly EventRegistrationRepository $eventRegistrationRepository,
        private readonly AuthorizationCheckerInterface $authorizationChecker,
    ) {
    }

    /** @param BaseEvent $object */
    public function normalize($object, $format = null, array $context = [])
    {
        $context[self::EVENT_USER_REGISTRATION_ALREADY_CALLED] = true;

        $event = $this->normalizer->normalize($object, $format, $context);

        $apiContext = $context[EventContextBuilder::CONTEXT_KEY] ?? null;
        $user = $this->getUser();

        if (EventContextBuilder::CONTEXT_PUBLIC_CONNECTED_USER === $apiContext) {
            if ($user) {
                $registration = $this->eventRegistrationRepository->findAdherentRegistration($object->getUuid()->toString(), $user->getUuid()->toString());

                $event['user_registered_at'] = $registration?->getCreatedAt()->format(\DateTimeInterface::RFC3339);
            }
        } elseif (EventContextBuilder::CONTEXT_PRIVATE === $apiContext) {
            $event['editable'] = $this->authorizationChecker->isGranted(CanManageEventVoter::PERMISSION, $object);
        }

        return $this->cleanEventDataIfNeed($object, $user, $event, $apiContext);
    }

    public function supportsNormalization($data, $format = null, array $context = []): bool
    {
        return
            empty($context[self::EVENT_USER_REGISTRATION_ALREADY_CALLED])
            && !empty($context[EventContextBuilder::CONTEXT_KEY])
            && $data instanceof BaseEvent;
    }

    private function cleanPrivateEvent(array $event): array
    {
        foreach ($event as $key => $value) {
            if (!\in_array($key, ['name', 'uuid', 'slug', 'time_zone', 'begin_at', 'finish_at', 'status', 'visibility', 'image_url', 'link'])) {
                $event[$key] = null;
            }
        }

        return $event;
    }

    private function cleanEventDataIfNeed(BaseEvent $event, ?Adherent $adherent, array $eventData, string $apiContext): array
    {
        if (EventContextBuilder::CONTEXT_PRIVATE === $apiContext) {
            return $eventData;
        }

        $needClean =
            (EventContextBuilder::CONTEXT_PUBLIC_ANONYMOUS === $apiContext && !$event->isPublic())
            || (
                EventContextBuilder::CONTEXT_PUBLIC_CONNECTED_USER === $apiContext
                && $event->isForAdherent()
                && (
                    !$adherent
                    || (EventVisibilityEnum::ADHERENT === $event->visibility && !$adherent->hasTag(TagEnum::ADHERENT))
                    || (EventVisibilityEnum::ADHERENT_DUES === $event->visibility && !$adherent->hasTag(TagEnum::getAdherentYearTag()))
                )
            );

        if ($needClean) {
            $eventData = $this->cleanPrivateEvent($eventData);
            $eventData['object_state'] = 'partial';
        } else {
            $eventData['object_state'] = 'full';
        }

        return $eventData;
    }

    private function getUser(): ?Adherent
    {
        if (($user = $this->security->getUser()) && $user instanceof Adherent) {
            return $user;
        }

        return null;
    }
}