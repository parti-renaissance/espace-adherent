<?php

namespace App\Normalizer;

use App\Entity\Projection\ManagedUser;
use App\ManagedUsers\ManagedUsersFilter;
use App\Repository\AdherentMandate\ElectedRepresentativeAdherentMandateRepository;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ManagedUserNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    public const FILTER_PARAM = 'filter';
    private const MANAGED_USER_NORMALIZER_ALREADY_CALLED = 'MANAGED_USER_NORMALIZER_ALREADY_CALLED';

    public function __construct(private readonly ElectedRepresentativeAdherentMandateRepository $erMandateRepository)
    {
    }

    /** @param ManagedUser $object */
    public function normalize($object, $format = null, array $context = [])
    {
        $context[self::MANAGED_USER_NORMALIZER_ALREADY_CALLED] = true;

        $data = $this->normalizer->normalize($object, $format, $context);

        $data['email_subscription'] = null;

        if (!empty($context[self::FILTER_PARAM]) && $context[self::FILTER_PARAM] instanceof ManagedUsersFilter) {
            /** @var ManagedUsersFilter $filter */
            $filter = $context[self::FILTER_PARAM];

            if ($filter->getSubscriptionType()) {
                $data['email_subscription'] = \in_array($filter->getSubscriptionType(), $object->getSubscriptionTypes(), true);
            }
        }

        $groups = $context['groups'] ?? [];

        if (\in_array('managed_user_read', $groups, true)) {
            $currentMandates = $this->erMandateRepository->findCurrentMandates($object->getOriginalId());

            $data['current_mandates'] = $this->normalizer->normalize($currentMandates, $format, $context);
        }

        return $data;
    }

    public function supportsNormalization($data, $format = null, array $context = [])
    {
        return
            empty($context[self::MANAGED_USER_NORMALIZER_ALREADY_CALLED])
            && $data instanceof ManagedUser;
    }
}
