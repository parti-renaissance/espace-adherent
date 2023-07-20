<?php

namespace App\Normalizer;

use App\Entity\Projection\ManagedUser;
use App\ManagedUsers\ManagedUsersFilter;
use App\Repository\AdherentMandate\ElectedRepresentativeAdherentMandateRepository;
use Doctrine\Common\Collections\Criteria;
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
            $mandates = $this->erMandateRepository->findMandatesForAdherentId($object->getOriginalId());

            $currentMandates = $mandates->matching(
                Criteria::create()
                    ->andWhere(Criteria::expr()->eq('finishAt', null))
                    ->orderBy(['beginAt' => 'DESC'])
            )->toArray();

            $terminatedMandates = $mandates->matching(
                Criteria::create()
                    ->andWhere(Criteria::expr()->neq('finishAt', null))
                    ->orderBy(['beginAt' => 'DESC'])
            )->toArray();

            $data['current_mandates'] = $this->normalizer->normalize(array_values($currentMandates), $format, $context);
            $data['terminated_mandates'] = $this->normalizer->normalize(array_values($terminatedMandates), $format, $context);
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
