<?php

namespace App\Normalizer;

use App\Entity\Adherent;
use App\Entity\PushToken;
use App\OAuth\Model\DeviceApiUser;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

final class PushTokenDenormalizer implements DenormalizerInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;

    public function __construct(private readonly Security $security)
    {
    }

    public function denormalize($data, $class, $format = null, array $context = []): mixed
    {
        /** @var PushToken $data */
        $data = $this->denormalizer->denormalize($data, $class, $format, $context + [__CLASS__ => true]);

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

    public function getSupportedTypes(?string $format): array
    {
        return [
            '*' => null,
            PushToken::class => false,
        ];
    }

    public function supportsDenormalization($data, $type, $format = null, array $context = []): bool
    {
        return !isset($context[__CLASS__]) && is_a($type, PushToken::class, true) && $this->security->getUser();
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
