<?php

namespace App\Normalizer;

use App\Entity\ElectedRepresentative\Mandate;
use App\Entity\ElectedRepresentative\PoliticalFunction;
use App\Scope\ScopeGeneratorResolver;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class UpdateElectedMandateDenormalizer implements DenormalizerInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;

    private const ALREADY_CALLED = 'JE_MENGAGE_WEB_ELECTED_MANDATE_UPDATE_DENORMALIZER_ALREADY_CALLED';

    public function __construct(
        private readonly Security $security,
        private readonly ScopeGeneratorResolver $scopeGeneratorResolver
    ) {
    }

    public function denormalize($data, string $class, ?string $format = null, array $context = [])
    {
        $context[self::ALREADY_CALLED] = true;

        $scope = $this->scopeGeneratorResolver->generate();
        $adherent = $scope && $scope->getDelegatedAccess() ? $scope->getDelegator() : $this->security->getUser();

        $functionsData = $data['political_functions'] ?? null;
        unset($data['political_functions']);

        /** @var Mandate $mandate */
        $mandate = $this->denormalizer->denormalize($data, $class, $format, $context);

        if (\is_array($functionsData)) {
            foreach ($mandate->getPoliticalFunctions()->toArray() as $politicalFunction) {
                if (!\in_array($politicalFunction->getId(), $this->payloadIds($functionsData), true)) {
                    $mandate->removePoliticalFunction($politicalFunction);
                }
            }

            foreach ($functionsData as $functionData) {
                $function = $this->handleChanges($mandate, $functionData, $format, $context);

                if (!$mandate->getPoliticalFunctions()->contains($function)) {
                    $mandate->addPoliticalFunction($function);
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
            && 'api_mandates_put_item' === ($context['operation_name'] ?? null);
    }

    private function handleChanges(
        Mandate $mandate,
        array $functionData,
        ?string $format = null,
        array $context = []
    ): PoliticalFunction {
        if (isset($functionData['id'])) {
            $function = $this->denormalizer->denormalize($functionData['id'], PoliticalFunction::class, $format, $context);
        } else {
            $function = new PoliticalFunction();
        }

        $this->denormalizer->denormalize($functionData, PoliticalFunction::class, null, [
            AbstractNormalizer::OBJECT_TO_POPULATE => $function,
            AbstractNormalizer::GROUPS => ['elected_mandate_write'],
            AbstractObjectNormalizer::DISABLE_TYPE_ENFORCEMENT => true,
        ]);

        if (!$function->getElectedRepresentative()) {
            $function->setElectedRepresentative($mandate->getElectedRepresentative());
        }

        return $function;
    }

    private function payloadIds(array $data): array
    {
        return array_map('intval', array_column($data, 'id'));
    }
}
