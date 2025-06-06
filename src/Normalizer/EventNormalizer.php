<?php

namespace App\Normalizer;

use App\Adherent\Tag\TagEnum;
use App\Api\Serializer\PrivatePublicContextBuilder;
use App\Entity\Adherent;
use App\Entity\Event\Event;
use App\Event\EventCleaner;
use App\Event\EventVisibilityEnum;
use App\Repository\EventRegistrationRepository;
use App\Security\Voter\Event\CanManageEventVoter;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Http\LoginLink\LoginLinkHandlerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class EventNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    public function __construct(
        private readonly Security $security,
        private readonly EventRegistrationRepository $eventRegistrationRepository,
        private readonly AuthorizationCheckerInterface $authorizationChecker,
        private readonly EventCleaner $eventCleaner,
        private readonly LoginLinkHandlerInterface $loginLinkHandler,
    ) {
    }

    /** @param Event $object */
    public function normalize($object, $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        $event = $this->normalizer->normalize($object, $format, $context + [__CLASS__ => true]);

        $apiContext = $context[PrivatePublicContextBuilder::CONTEXT_KEY] ?? null;
        $user = $this->getUser();
        $event['editable'] = false;

        if (\in_array($apiContext, [PrivatePublicContextBuilder::CONTEXT_PRIVATE, PrivatePublicContextBuilder::CONTEXT_PUBLIC_CONNECTED_USER])) {
            if (PrivatePublicContextBuilder::CONTEXT_PUBLIC_CONNECTED_USER === $apiContext) {
                $registration = $this->eventRegistrationRepository->findAdherentRegistration($object->getUuidAsString(), $user->getUuidAsString());
                $event['user_registered_at'] = $registration?->getCreatedAt()->format(\DateTimeInterface::RFC3339);
            }

            $event['editable'] = $this->authorizationChecker->isGranted(CanManageEventVoter::CAN_MANAGE_EVENT, $object);

            if ($event['editable']) {
                $event['edit_link'] = $this->loginLinkHandler->createLoginLink($user, targetPath: '/cadre?state='.urlencode('/evenements/'.$object->getUuidAsString().'?scope='.$object->getAuthorScope()))->getUrl();
            }
        }

        return $this->cleanEventDataIfNeed($object, $user, $event, $apiContext);
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            Event::class => false,
        ];
    }

    public function supportsNormalization($data, $format = null, array $context = []): bool
    {
        return
            !isset($context[__CLASS__])
            && !empty($context[PrivatePublicContextBuilder::CONTEXT_KEY])
            && $data instanceof Event;
    }

    private function cleanEventDataIfNeed(Event $event, ?Adherent $adherent, array $eventData, string $apiContext): array
    {
        if (PrivatePublicContextBuilder::CONTEXT_PRIVATE === $apiContext) {
            return $eventData;
        }

        $needClean = true;

        if (true === $eventData['editable'] || $event->isPublic()) {
            $needClean = false;
        } elseif ($adherent) {
            $hasAccess =
                $event->isPrivate()
                || (EventVisibilityEnum::ADHERENT === $event->visibility && $adherent->hasTag(TagEnum::ADHERENT))
                || (EventVisibilityEnum::ADHERENT_DUES === $event->visibility && $adherent->hasTag(TagEnum::getAdherentYearTag()))
                || ($event->isInvitation() && $this->eventRegistrationRepository->findAdherentRegistration($event->getUuidAsString(), $adherent->getUuidAsString(), null));

            if ($hasAccess) {
                $needClean = false;
            }
        }

        if ($needClean) {
            $eventData = $this->eventCleaner->cleanEventData($eventData);
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
