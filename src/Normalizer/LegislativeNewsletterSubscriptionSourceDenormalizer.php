<?php

declare(strict_types=1);

namespace App\Normalizer;

use ApiPlatform\Metadata\Exception\ItemNotFoundException;
use App\Entity\Geo\Zone;
use App\Entity\LegislativeNewsletterSubscription;
use App\Repository\Geo\ZoneRepository;
use App\Repository\LegislativeNewsletterSubscriptionRepository;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class LegislativeNewsletterSubscriptionSourceDenormalizer implements DenormalizerInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;

    public function __construct(
        private readonly LegislativeNewsletterSubscriptionRepository $subscriptionRepository,
        private readonly ZoneRepository $zoneRepository,
    ) {
    }

    public function denormalize($data, $type, $format = null, array $context = []): mixed
    {
        $context[AbstractNormalizer::OBJECT_TO_POPULATE] = isset($data['email_address']) ? $this->subscriptionRepository->findOneBy(['emailAddress' => $data['email_address']]) : null;

        if (isset($context['api_allow_update']) && true !== $context['api_allow_update']) {
            $context['api_allow_update'] = true;
        }

        /** @var LegislativeNewsletterSubscription $subscription */
        $subscription = $this->denormalizer->denormalize($data, $type, $format, $context + [__CLASS__ => true]);

        if (isset($data['from_zone'])) {
            if (!$zone = $this->zoneRepository->findOneBy(['type' => [Zone::DISTRICT, Zone::FOREIGN_DISTRICT], 'code' => $data['from_zone']])) {
                throw new ItemNotFoundException('District zone not found.');
            }

            $subscription->addFromZone($zone);
        }

        return $subscription;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            LegislativeNewsletterSubscription::class => false,
        ];
    }

    public function supportsDenormalization($data, $type, $format = null, array $context = []): bool
    {
        return !isset($context[__CLASS__]) && LegislativeNewsletterSubscription::class === $type;
    }
}
