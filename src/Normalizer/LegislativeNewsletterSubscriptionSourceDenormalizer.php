<?php

namespace App\Normalizer;

use ApiPlatform\Core\Exception\ItemNotFoundException;
use App\Entity\Geo\Zone;
use App\Entity\LegislativeNewsletterSubscription;
use App\Repository\Geo\ZoneRepository;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class LegislativeNewsletterSubscriptionSourceDenormalizer implements DenormalizerInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;

    private const ALREADY_CALLED = 'LEGISLATIVE_NEWSLETTER_SUBSCRIPTION_ALREADY_CALLED';

    private ZoneRepository $zoneRepository;

    public function __construct(ZoneRepository $zoneRepository)
    {
        $this->zoneRepository = $zoneRepository;
    }

    public function denormalize($data, $type, $format = null, array $context = [])
    {
        $context[self::ALREADY_CALLED] = true;

        /** @var LegislativeNewsletterSubscription $subscription */
        $subscription = $this->denormalizer->denormalize($data, $type, $format, $context);

        if (isset($data['from_zone'])) {
            if (!$zone = $this->zoneRepository->findOneBy(['type' => Zone::DISTRICT, 'code' => $data['from_zone']])) {
                throw new ItemNotFoundException('District zone not found.');
            }

            $subscription->setFromZone($zone);
        }

        return $subscription;
    }

    public function supportsDenormalization($data, $type, $format = null, array $context = [])
    {
        return !isset($context[self::ALREADY_CALLED])
            && LegislativeNewsletterSubscription::class === $type;
    }
}
