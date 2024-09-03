<?php

namespace App\Normalizer;

use ApiPlatform\Exception\ItemNotFoundException;
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

    private const ALREADY_CALLED = 'LEGISLATIVE_NEWSLETTER_SUBSCRIPTION_ALREADY_CALLED';

    private LegislativeNewsletterSubscriptionRepository $subscriptionRepository;
    private ZoneRepository $zoneRepository;

    public function __construct(
        LegislativeNewsletterSubscriptionRepository $subscriptionRepository,
        ZoneRepository $zoneRepository,
    ) {
        $this->subscriptionRepository = $subscriptionRepository;
        $this->zoneRepository = $zoneRepository;
    }

    public function denormalize($data, $type, $format = null, array $context = [])
    {
        $context[self::ALREADY_CALLED] = true;
        $context[AbstractNormalizer::OBJECT_TO_POPULATE] = isset($data['email_address']) ? $this->subscriptionRepository->findOneBy(['emailAddress' => $data['email_address']]) : null;

        if (isset($context['api_allow_update']) && true !== $context['api_allow_update']) {
            $context['api_allow_update'] = true;
        }

        /** @var LegislativeNewsletterSubscription $subscription */
        $subscription = $this->denormalizer->denormalize($data, $type, $format, $context);

        if (isset($data['from_zone'])) {
            if (!$zone = $this->zoneRepository->findOneBy(['type' => [Zone::DISTRICT, Zone::FOREIGN_DISTRICT], 'code' => $data['from_zone']])) {
                throw new ItemNotFoundException('District zone not found.');
            }

            $subscription->addFromZone($zone);
        }

        return $subscription;
    }

    public function supportsDenormalization($data, $type, $format = null, array $context = [])
    {
        return !isset($context[self::ALREADY_CALLED])
            && LegislativeNewsletterSubscription::class === $type;
    }
}
