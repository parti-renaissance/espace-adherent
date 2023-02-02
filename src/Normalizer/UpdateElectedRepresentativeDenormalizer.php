<?php

namespace App\Normalizer;

use App\Entity\ElectedRepresentative\ElectedRepresentative;
use App\Entity\ElectedRepresentative\Mandate;
use App\Entity\ElectedRepresentative\PoliticalFunction;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class UpdateElectedRepresentativeDenormalizer implements DenormalizerInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;

    private const ALREADY_CALLED = 'JE_MENGAGE_WEB_ELECTED_REPRESENTATIVE_UPDATE_DENORMALIZER_ALREADY_CALLED';

    public function denormalize($data, string $class, string $format = null, array $context = [])
    {
        $context[self::ALREADY_CALLED] = true;

        $mandatesData = $data['mandates'] ?? null;
        unset($data['mandates']);

        /** @var ElectedRepresentative $electedRepresentative */
        $electedRepresentative = $this->denormalizer->denormalize($data, $class, $format, $context);

        if (\is_array($mandatesData)) {
            foreach ($electedRepresentative->getMandates()->toArray() as $mandate) {
                if (!\in_array($mandate->getId(), $this->payloadIds($mandatesData), true)) {
                    $electedRepresentative->removeMandate($mandate);
                }
            }

            foreach ($mandatesData as $mandateData) {
                $mandate = $this->handleChanges($electedRepresentative, $mandateData, $format, $context);

                if (!$electedRepresentative->getMandates()->contains($mandate)) {
                    $electedRepresentative->addMandate($mandate);
                }
            }
        }

        return $electedRepresentative;
    }

    public function supportsDenormalization($data, string $type, string $format = null, array $context = [])
    {
        return !isset($context[self::ALREADY_CALLED])
            && is_a($type, ElectedRepresentative::class, true)
            && 'api_elected_representatives_put_item' === ($context['operation_name'] ?? null)
        ;
    }

    private function handleChanges(
        ElectedRepresentative $electedRepresentative,
        array $requestMandate,
        string $format = null,
        array $context = []
    ): Mandate {
        if (isset($requestMandate['id'])) {
            $mandate = $this->denormalizer->denormalize($requestMandate['id'], Mandate::class, $format, $context);
        } else {
            $mandate = new Mandate();
        }

        $functionsData = $requestMandate['political_functions'] ?? null;
        unset($requestMandate['political_functions']);

        $this->denormalizer->denormalize($requestMandate, Mandate::class, null, [
            AbstractNormalizer::OBJECT_TO_POPULATE => $mandate,
            AbstractNormalizer::GROUPS => ['elected_representative_write'],
            AbstractObjectNormalizer::DISABLE_TYPE_ENFORCEMENT => true,
        ]);

        $this->applyPoliticalFunctionChanges($mandate, $electedRepresentative, $functionsData, $format, $context);

        return $mandate;
    }

    private function applyPoliticalFunctionChanges(
        Mandate $mandate,
        ElectedRepresentative $electedRepresentative,
        array $politicalFunctions,
        string $format,
        array $context = []
    ): void {
        if (!$mandate->getPoliticalFunctions()->isEmpty()) {
            foreach ($mandate->getPoliticalFunctions()->toArray() as $politicalFunction) {
                if (!\in_array($politicalFunction->getId(), $this->payloadIds($politicalFunctions), true)) {
                    $mandate->removePoliticalFunction($politicalFunction);
                }
            }
        }

        foreach ($politicalFunctions as $functionData) {
            if (isset($functionData['id'])) {
                $function = $this->denormalizer->denormalize($functionData['id'], PoliticalFunction::class, $format, $context);
            } else {
                $function = new PoliticalFunction();
            }

            $this->denormalizer->denormalize($functionData, PoliticalFunction::class, null, [
                AbstractNormalizer::OBJECT_TO_POPULATE => $function,
                AbstractNormalizer::GROUPS => ['elected_representative_write'],
                AbstractObjectNormalizer::DISABLE_TYPE_ENFORCEMENT => true,
            ]);

            if (!$function->getElectedRepresentative()) {
                $function->setElectedRepresentative($electedRepresentative);
            }

            if (!$mandate->getPoliticalFunctions()->contains($function)) {
                $mandate->addPoliticalFunction($function);
            }
        }
    }

    private function payloadIds(array $data): array
    {
        return array_map('intval', array_column($data, 'id'));
    }
}
