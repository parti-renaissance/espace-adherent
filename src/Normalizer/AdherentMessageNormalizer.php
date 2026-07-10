<?php

declare(strict_types=1);

namespace App\Normalizer;

use App\AdherentMessage\Variable\Renderer;
use App\Controller\Api\AdherentMessage\GetAvailableSendersController;
use App\Entity\AdherentMessage\AdherentMessage;
use App\Mailchimp\Campaign\Audience\SendStatusFactory;
use App\Security\Voter\PublicationVoter;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class AdherentMessageNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly AuthorizationCheckerInterface $authorizationChecker,
        private readonly Renderer $variableRenderer,
        private readonly Security $security,
        private readonly SendStatusFactory $sendStatusFactory,
    ) {
    }

    /** @param AdherentMessage $object */
    public function normalize($object, $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        $data = $this->normalizer->normalize($object, $format, $context + [__CLASS__ => true]);

        $groups = $context['groups'] ?? [];

        if (\in_array('message_read', $groups, true) && $user = $this->security->getUser()) {
            $data['json_content'] = $this->variableRenderer->renderTipTap($data['json_content'] ?? '', $user);
            $data['subject'] = $this->variableRenderer->renderPlain($data['subject'] ?? '', $user);
        }

        if (\in_array('message_read', $groups, true) && $campaign = ($object->getMailchimpCampaigns()[0] ?? null)) {
            $data['send_status'] = $this->sendStatusFactory->build($campaign);
        }

        if (array_intersect($groups, ['message_read_list', 'message_read'])) {
            $data['author']['scope'] = $object->getAuthorScope();

            $data['sender'] = array_merge([
                'uuid' => null,
                'first_name' => GetAvailableSendersController::CHOICE_LABEL,
                'last_name' => null,
                'image_url' => null,
                'instance' => $object->senderInstance,
                'role' => $object->senderRole,
                'zone' => $object->senderZone,
                'theme' => $object->senderTheme,
            ], $data['sender'] ?? [], );

            $data['editable'] = $this->authorizationChecker->isGranted(PublicationVoter::PERMISSION, $object);

            if (!$data['editable'] || $object->isStatutory()) {
                unset($data['statistics']);
            }

            if ($data['editable']) {
                if (!$object->isStatutory()) {
                    $data['preview_link'] = $object->isSent() ? null : $this->urlGenerator->generate(
                        'app_renaissance_adherent_message_preview',
                        ['uuid' => $object->getUuid()->toString()],
                        UrlGeneratorInterface::ABSOLUTE_URL
                    );
                }
            } else {
                foreach (array_keys($data) as $key) {
                    if (!\in_array($key, ['uuid', 'sender', 'json_content', 'sent_at', 'subject', 'updated_at', 'statistics'])) {
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
