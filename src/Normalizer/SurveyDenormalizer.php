<?php

namespace App\Normalizer;

use ApiPlatform\Metadata\HttpOperation;
use App\Entity\Jecoute\LocalSurvey;
use App\Entity\Jecoute\NationalSurvey;
use App\Entity\Jecoute\Survey;
use App\Jecoute\SurveyTypeEnum;
use Symfony\Component\Serializer\Exception\UnexpectedValueException;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class SurveyDenormalizer implements DenormalizerInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;

    public function denormalize($data, $type, $format = null, array $context = []): mixed
    {
        if (!empty($context[AbstractNormalizer::OBJECT_TO_POPULATE])) {
            $surveyClass = \get_class($context[AbstractNormalizer::OBJECT_TO_POPULATE]);
        } else {
            $surveyType = $data['type'] ?? null;

            if (!$surveyType || !($surveyClass = $this->getSurveyClassFromType($surveyType))) {
                throw new UnexpectedValueException('Type value is missing or invalid');
            }

            unset($data['type']);
        }

        if (!$surveyClass) {
            throw new UnexpectedValueException('Type value is missing or invalid');
        }

        $context['resource_class'] = $surveyClass;
        /** @var HttpOperation $operation */
        $operation = $context['operation'];
        $context['operation'] = $operation->withClass($surveyClass);

        return $this->denormalizer->denormalize($data, $surveyClass, $format, $context + [__CLASS__ => true]);
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            '*' => null,
            Survey::class => false,
        ];
    }

    public function supportsDenormalization($data, $type, $format = null, array $context = []): bool
    {
        return !isset($context[__CLASS__])
            && is_a($type, Survey::class, true)
            && '_api_/v3/surveys_post' === ($context['operation_name'] ?? null);
    }

    private function getSurveyClassFromType(string $surveyType): ?string
    {
        switch ($surveyType) {
            case SurveyTypeEnum::NATIONAL:
                return NationalSurvey::class;
            case SurveyTypeEnum::LOCAL:
                return LocalSurvey::class;
        }

        return null;
    }
}
