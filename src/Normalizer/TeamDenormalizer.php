<?php

namespace App\Normalizer;

use App\Entity\Adherent;
use App\Entity\Team\Team;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

final class TeamDenormalizer implements DenormalizerInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;

    private const ALREADY_CALLED = 'TEAM_DENORMALIZER_ALREADY_CALLED';

    private Security $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function denormalize($data, $class, $format = null, array $context = [])
    {
        $context[self::ALREADY_CALLED] = true;

        $data = $this->denormalizer->denormalize($data, $class, $format, $context);

        if (!$data->getId()) {
            $data->setCreatedByAdherent($this->security->getUser());
        }
        $data->setUpdatedByAdherent($this->security->getUser());

        return $data;
    }

    public function supportsDenormalization($data, $type, $format = null, array $context = [])
    {
        if (isset($context[self::ALREADY_CALLED])) {
            return false;
        }

        return
            is_a($type, Team::class, true)
            && $this->security->getUser() instanceof Adherent
            && \in_array('team_write', $context['groups'] ?? [])
        ;
    }
}
