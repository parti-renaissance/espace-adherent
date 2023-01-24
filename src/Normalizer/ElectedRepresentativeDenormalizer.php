<?php

namespace App\Normalizer;

use App\Entity\ElectedRepresentative\ElectedRepresentative;
use App\Entity\ElectedRepresentative\Mandate;
use App\Entity\ElectedRepresentative\PoliticalFunction;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class ElectedRepresentativeDenormalizer implements DenormalizerInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;

    private const ALREADY_CALLED = 'JE_MENGAGE_WEB_ELECTED_REPRESENTATIVE_DENORMALIZER_ALREADY_CALLED';

    public function denormalize($data, string $class, string $format = null, array $context = [])
    {
        $context[self::ALREADY_CALLED] = true;

        /** @var ElectedRepresentative $electedRepresentative */
        $electedRepresentative = $this->denormalizer->denormalize($data, $class, $format, $context);

        if ($mandates = $electedRepresentative->getMandates()) {
            /** @var Mandate $mandate */
            foreach ($mandates as $mandate) {
                if ($functions = $mandate->getPoliticalFunctions()) {
                    /** @var PoliticalFunction $function */
                    foreach ($functions as $function) {
                        if (!$function->getElectedRepresentative()) {
                            $function->setElectedRepresentative($electedRepresentative);
                        }
                    }
                }
            }
        }

        return $electedRepresentative;
    }

    public function supportsDenormalization($data, string $type, string $format = null, array $context = [])
    {
        return !isset($context[self::ALREADY_CALLED])
            && is_a($type, ElectedRepresentative::class, true)
            && \in_array('elected_representative_write', $context['groups'] ?? [])
        ;
    }
}
