<?php

namespace App\Normalizer;

use App\Entity\Projection\ManagedUser;
use App\ManagedUsers\ManagedUsersFilter;
use App\Membership\MandatesEnum;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class ManagedUserNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    public const FILTER_PARAM = 'filter';
    private const MANAGED_USER_NORMALIZER_ALREADY_CALLED = 'MANAGED_USER_NORMALIZER_ALREADY_CALLED';

    public function __construct(private readonly TranslatorInterface $translator)
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

        if (\in_array('managed_user_read', $context['groups'] ?? [])) {
            if (\array_key_exists('declared_mandates', $data) && !empty($data['declared_mandates'])) {
                $translatedMandates = [];

                foreach ($data['declared_mandates'] as $mandate) {
                    $translatedMandate = array_search($mandate, MandatesEnum::CHOICES);

                    $translatedMandates[] = $translatedMandate
                        ? $this->translator->trans($translatedMandate)
                        : $mandate;
                }

                $data['declared_mandates'] = $translatedMandates;
            }
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
