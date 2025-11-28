<?php

declare(strict_types=1);

namespace App\Normalizer;

use App\Entity\ElectedRepresentative\Mandate;
use App\Entity\ElectedRepresentative\PoliticalFunction;
use App\Scope\ScopeGeneratorResolver;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class ElectedMandateDenormalizer implements DenormalizerInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;

    public function __construct(
        private readonly Security $security,
        private readonly ScopeGeneratorResolver $scopeGeneratorResolver,
    ) {
    }

    public function denormalize($data, string $class, ?string $format = null, array $context = []): mixed
    {
        $scope = $this->scopeGeneratorResolver->generate();
        $adherent = $scope && $scope->getDelegatedAccess() ? $scope->getDelegator() : $this->security->getUser();

        /** @var Mandate $mandate */
        $mandate = $this->denormalizer->denormalize($data, $class, $format, $context + [__CLASS__ => true]);

        if ($functions = $mandate->getPoliticalFunctions()) {
            /** @var PoliticalFunction $function */
            foreach ($functions as $function) {
                if (!$function->getElectedRepresentative()) {
                    $function->setElectedRepresentative($mandate->getElectedRepresentative());
                }
            }
        }

        $mandate->getElectedRepresentative()->setUpdatedByAdherent($adherent);

        return $mandate;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            Mandate::class => false,
        ];
    }

    public function supportsDenormalization($data, string $type, ?string $format = null, array $context = []): bool
    {
        return !isset($context[__CLASS__])
            && is_a($type, Mandate::class, true)
            && '_api_/v3/elected_mandates_post' === ($context['operation_name'] ?? null);
    }
}
