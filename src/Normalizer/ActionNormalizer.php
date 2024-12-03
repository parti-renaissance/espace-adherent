<?php

namespace App\Normalizer;

use App\Api\Serializer\PrivatePublicContextBuilder;
use App\Entity\Action\Action;
use App\Entity\Adherent;
use App\Repository\Action\ActionParticipantRepository;
use App\Security\Voter\CanManageActionVoter;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ActionNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    public const ACTION_NORMALIZER_ALREADY_CALLED = 'action_normalizer';

    private ?array $cache = null;

    public function __construct(
        private readonly Security $security,
        private readonly ActionParticipantRepository $actionParticipantRepository,
        private readonly AuthorizationCheckerInterface $authorizationChecker,
    ) {
    }

    /** @param Action $object */
    public function normalize($object, $format = null, array $context = [])
    {
        $context[self::ACTION_NORMALIZER_ALREADY_CALLED] = true;

        $action = $this->normalizer->normalize($object, $format, $context);

        $apiContext = $context[PrivatePublicContextBuilder::CONTEXT_KEY] ?? null;

        if (PrivatePublicContextBuilder::CONTEXT_PUBLIC_CONNECTED_USER === $apiContext) {
            if ($user = $this->getUser()) {
                $adherentUuid = $user->getUuid()->toString();
                $registrationDate = $this->getRegistrationDate($object->getId(), $adherentUuid);
                $action['user_registered_at'] = $registrationDate?->format(\DateTimeInterface::RFC3339);
            }
        }

        $action['editable'] = $this->authorizationChecker->isGranted(CanManageActionVoter::PERMISSION, $object);

        return $action;
    }

    public function supportsNormalization($data, $format = null, array $context = []): bool
    {
        return
            empty($context[self::ACTION_NORMALIZER_ALREADY_CALLED])
            && !empty($context[PrivatePublicContextBuilder::CONTEXT_KEY])
            && $data instanceof Action;
    }

    private function getUser(): ?Adherent
    {
        if (($user = $this->security->getUser()) && $user instanceof Adherent) {
            return $user;
        }

        return null;
    }

    private function getRegistrationDate(int $actionId, string $adherentUuid): ?\DateTime
    {
        if (null === $this->cache) {
            $this->cache = $this->actionParticipantRepository->findAllRegistrationDates($adherentUuid);
        }

        return $this->cache[$actionId] ?? null;
    }
}
