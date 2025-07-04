<?php

namespace App\Normalizer;

use App\AdherentMessage\AdherentMessageFactory;
use App\Api\Serializer\PrivatePublicContextBuilder;
use App\Entity\AdherentMessage\AbstractAdherentMessage;
use App\Entity\AdherentMessage\AdherentMessageInterface;
use App\MyTeam\RoleEnum;
use App\Repository\MyTeam\MemberRepository;
use App\Repository\MyTeam\MyTeamRepository;
use App\Scope\ScopeGeneratorResolver;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class AdherentMessageDenormalizer implements DenormalizerInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;

    public function __construct(
        private readonly ScopeGeneratorResolver $resolver,
        private readonly MemberRepository $memberRepository,
        private readonly MyTeamRepository $myTeamRepository,
    ) {
    }

    public function denormalize($data, $type, $format = null, array $context = []): mixed
    {
        /** @var AdherentMessageInterface|null $oldMessage */
        $oldMessage = null;

        $scope = $this->resolver->generate();

        if (!empty($context[AbstractNormalizer::OBJECT_TO_POPULATE])) {
            $messageClass = \get_class($context[AbstractNormalizer::OBJECT_TO_POPULATE]);
            $oldMessage = clone $context[AbstractNormalizer::OBJECT_TO_POPULATE];
        } else {
            $messageClass = AdherentMessageFactory::getMessageClassName($scope?->getMainCode() ?? '', $data['type'] ?? null);
        }

        unset($data['type']);

        if (!isset($context[AbstractNormalizer::OBJECT_TO_POPULATE])) {
            $context[AbstractNormalizer::OBJECT_TO_POPULATE] = new $messageClass();
        }

        /** @var AdherentMessageInterface $message */
        $message = $this->denormalizer->denormalize($data, $messageClass, $format, $context + [__CLASS__ => true]);

        if (!$message->getLabel()) {
            $message->setLabel($message->getSubject());
        }

        $message->setSource(PrivatePublicContextBuilder::CONTEXT_PRIVATE === $context[PrivatePublicContextBuilder::CONTEXT_KEY] ? AdherentMessageInterface::SOURCE_CADRE : AdherentMessageInterface::SOURCE_VOX);

        $message->setAuthorScope(null);

        if (!$message->getSender() && $scope) {
            $message->setSender($scope->getMainUser());
        }

        if ($message->getSender() && $message->getSender() !== $message->getAuthor()) {
            $message->setAuthorRole(null);
            $sender = $message->getSender();

            if (
                $sender
                && $scope
                && ($team = $this->myTeamRepository->findOneByAdherentAndScope($teamOwner = $scope->getMainUser(), $scope->getMainCode()))
            ) {
                if ($teamOwner === $sender) {
                    $message->setAuthorRole($scope->getMainRoleName());
                } elseif ($member = $this->memberRepository->findMemberInTeam($team, $sender)) {
                    $message->setAuthorRole(RoleEnum::LABELS[$member->getRole()] ?? $member->getRole());
                }
            }
        }

        if (
            ($context['operation_name'] ?? null) === '_api_/v3/adherent_messages/{uuid}_put'
            && $oldMessage
            && (
                $oldMessage->getContent() !== $message->getContent()
                || $oldMessage->getSubject() !== $message->getSubject()
            )
        ) {
            $message->setSynchronized(false);
        }

        return $message;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [AbstractAdherentMessage::class => false];
    }

    public function supportsDenormalization($data, $type, $format = null, array $context = []): bool
    {
        return !isset($context[__CLASS__]) && AbstractAdherentMessage::class === $type;
    }
}
