<?php

namespace App\Normalizer;

use App\Adherent\Tag\TagEnum;
use App\Api\Serializer\PrivatePublicContextBuilder;
use App\Entity\Adherent;
use App\Entity\Event\BaseEvent;
use App\Entity\Event\CommitteeEvent;
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

    /** @param BaseEvent $object */
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

            $event['editable'] = PrivatePublicContextBuilder::CONTEXT_PRIVATE === $apiContext ?
                $this->authorizationChecker->isGranted(CanManageEventVoter::CAN_MANAGE_EVENT, $object) :
                $this->authorizationChecker->isGranted(CanManageEventVoter::CAN_MANAGE_EVENT_ITEM, [
                    'instance' => $object->getAuthorInstance(),
                    'zones' => $object->getZones()->toArray(),
                    'committee_uuid' => $object instanceof CommitteeEvent ? $object->getCommitteeUuid() : null,
                ]);

            if ($event['editable']) {
                $event['edit_link'] = $this->loginLinkHandler->createLoginLink($user, targetPath: '/cadre?state='.urlencode('/evenements/'.$object->getUuidAsString().'?scope='.$object->getAuthorScope()))->getUrl();
            }
        }

        return $this->cleanEventDataIfNeed($object, $user, $event, $apiContext);
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            '*' => null,
            BaseEvent::class => false,
        ];
    }

    public function supportsNormalization($data, $format = null, array $context = []): bool
    {
        return
            !isset($context[__CLASS__])
            && !empty($context[PrivatePublicContextBuilder::CONTEXT_KEY])
            && $data instanceof BaseEvent;
    }

    private function cleanEventDataIfNeed(BaseEvent $event, ?Adherent $adherent, array $eventData, string $apiContext): array
    {
        if (PrivatePublicContextBuilder::CONTEXT_PRIVATE === $apiContext) {
            return $eventData;
        }

        $needClean =
            true !== $eventData['editable']
            && ((PrivatePublicContextBuilder::CONTEXT_PUBLIC_ANONYMOUS === $apiContext && !$event->isPublic())
            || (
                PrivatePublicContextBuilder::CONTEXT_PUBLIC_CONNECTED_USER === $apiContext
                && $event->isForAdherent()
                && (
                    !$adherent
                    || (EventVisibilityEnum::ADHERENT === $event->visibility && !$adherent->hasTag(TagEnum::ADHERENT))
                    || (EventVisibilityEnum::ADHERENT_DUES === $event->visibility && !$adherent->hasTag(TagEnum::getAdherentYearTag()))
                )
            ));

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
