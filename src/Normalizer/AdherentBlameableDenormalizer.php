<?php

namespace App\Normalizer;

use App\Entity\Adherent;
use App\Entity\EntityAdherentBlameableInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class AdherentBlameableDenormalizer implements DenormalizerInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;

    private const ALREADY_CALLED = 'ADHERENT_BLAMEABLE_DENORMALIZER_ALREADY_CALLED';

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
        // Make sure we're not called twice
        if (isset($context[self::ALREADY_CALLED])) {
            return false;
        }

        return is_a($type, EntityAdherentBlameableInterface::class, true) && $this->security->getUser() instanceof Adherent;
    }
}
