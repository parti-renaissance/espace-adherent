<?php

declare(strict_types=1);

namespace App\Normalizer;

use App\AdherentMessage\Variable\Renderer;
use App\Entity\AdherentMessage\AdherentMessage;
use App\JeMengage\Hit\Stats\AggregatorInterface;
use App\JeMengage\Hit\TargetTypeEnum;
use App\Mailchimp\Campaign\MailchimpObjectIdMapping;
use App\Security\Voter\PublicationVoter;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class AdherentMessageNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    public function __construct(
        private readonly AggregatorInterface $statisticsAggregator,
        private readonly MailchimpObjectIdMapping $mailchimpObjectIdMapping,
        private readonly AuthorizationCheckerInterface $authorizationChecker,
        private readonly Renderer $variableRenderer,
        private readonly Security $security,
    ) {
    }

    /** @param AdherentMessage $object */
    public function normalize($object, $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        $data = $this->normalizer->normalize($object, $format, $context + [__CLASS__ => true]);

        $groups = $context['groups'] ?? [];

        if (\in_array('message_read', $groups, true) && $user = $this->security->getUser()) {
            $data['json_content'] = $this->variableRenderer->renderTipTap($data['json_content'] ?? '', $user);
        }

        if (array_intersect($groups, ['message_read_list', 'message_read'])) {
            $data['author']['scope'] = $object->getAuthorScope();

            if (!empty($data['sender'])) {
                $data['sender'] = array_merge($data['sender'], [
                    'instance' => $object->senderInstance,
                    'role' => $object->senderRole,
                    'zone' => $object->senderZone,
                    'theme' => $object->senderTheme,
                ]);
            }

            $data['editable'] = $this->authorizationChecker->isGranted(PublicationVoter::PERMISSION, $object);

            if ($data['editable']) {
                $data['statistics'] = $this->statisticsAggregator->getStats(TargetTypeEnum::Publication, $object->getUuid());
                $data['preview_link'] = $this->mailchimpObjectIdMapping->generateMailchimpPreviewLink($object->getMailchimpId());
            } else {
                foreach (array_keys($data) as $key) {
                    if (!\in_array($key, ['uuid', 'sender', 'json_content', 'sent_at', 'subject', 'updated_at'])) {
                        unset($data[$key]);
                    }
                }
            }
        }

        return $data;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            AdherentMessage::class => false,
        ];
    }

    public function supportsNormalization($data, $format = null, array $context = []): bool
    {
        return !isset($context[__CLASS__]) && $data instanceof AdherentMessage;
    }
}
