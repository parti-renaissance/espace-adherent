<?php

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

    private const ALREADY_CALLED = 'JE_MENGAGE_WEB_ELECTED_MANDATE_DENORMALIZER_ALREADY_CALLED';

    public function __construct(
        private readonly Security $security,
        private readonly ScopeGeneratorResolver $scopeGeneratorResolver,
    ) {
    }

    public function denormalize($data, string $class, ?string $format = null, array $context = [])
    {
        $context[self::ALREADY_CALLED] = true;

        $scope = $this->scopeGeneratorResolver->generate();
        $adherent = $scope && $scope->getDelegatedAccess() ? $scope->getDelegator() : $this->security->getUser();

        /** @var Mandate $mandate */
        $mandate = $this->denormalizer->denormalize($data, $class, $format, $context);

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

    public function supportsDenormalization($data, string $type, ?string $format = null, array $context = [])
    {
        return !isset($context[self::ALREADY_CALLED])
            && is_a($type, Mandate::class, true)
            && '_api_/elected_mandates_post' === ($context['operation_name'] ?? null);
    }
}
