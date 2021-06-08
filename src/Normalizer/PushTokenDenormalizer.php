<?php

namespace App\Normalizer;

use App\Entity\Adherent;
use App\Entity\PushToken;
use App\OAuth\Model\DeviceApiUser;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

final class PushTokenDenormalizer implements DenormalizerInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;

    private const ALREADY_CALLED = 'PUSH_TOKEN_DENORMALIZER_ALREADY_CALLED';

    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function denormalize($data, $class, $format = null, array $context = [])
    {
        $context[self::ALREADY_CALLED] = true;

        /** @var PushToken $data */
        $data = $this->denormalizer->denormalize($data, $class, $format, $context);

        if (!$data->getId()) {
            $user = $this->security->getUser();

            if ($this->isAdherentUser()) {
                $data->setAdherent($user);
            } elseif ($this->isDeviceUser()) {
                $data->setDevice($user->getDevice());
            }
        }

        return $data;
    }

    public function supportsDenormalization($data, $type, $format = null, array $context = [])
    {
        // Make sure we're not called twice
        if (isset($context[self::ALREADY_CALLED])) {
            return false;
        }

        return is_a($type, PushToken::class, true)
            && $this->security->getUser()
        ;
    }

    private function isAdherentUser(): bool
    {
        return $this->security->getUser() instanceof Adherent;
    }

    private function isDeviceUser(): bool
    {
        return $this->security->getUser() instanceof DeviceApiUser;
    }
}
