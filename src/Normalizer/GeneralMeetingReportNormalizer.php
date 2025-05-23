<?php

namespace App\Normalizer;

use App\Entity\GeneralMeeting\GeneralMeetingReport;
use App\Scope\Generator\ScopeGeneratorInterface;
use App\Scope\ScopeGeneratorResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class GeneralMeetingReportNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    private ?ScopeGeneratorInterface $currentScope = null;

    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly ScopeGeneratorResolver $scopeGeneratorResolver,
    ) {
    }

    /**
     * @param GeneralMeetingReport $object
     */
    public function normalize($object, $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        $data = $this->normalizer->normalize($object, $format, $context + [__CLASS__ => true]);

        if (array_intersect(['general_meeting_report_list_read', 'general_meeting_report_read'], $context['groups'] ?? [])) {
            $data['file_path'] = $object->hasFilePath() ? $this->getUrl($object) : null;
        }

        return $data;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            GeneralMeetingReport::class => false,
        ];
    }

    public function supportsNormalization($data, $format = null, array $context = []): bool
    {
        return !isset($context[__CLASS__]) && $data instanceof GeneralMeetingReport;
    }

    private function getUrl(GeneralMeetingReport $generalMeetingReport): string
    {
        $parameters = [
            'uuid' => $generalMeetingReport->getUuid()->toString(),
        ];

        if ($scope = $this->getCurrentScope()) {
            $parameters['scope'] = $scope->getCode();
        }

        return $this->urlGenerator->generate('_api_/v3/general_meeting_reports/{uuid}/file_get', $parameters, UrlGeneratorInterface::ABSOLUTE_URL);
    }

    private function getCurrentScope(): ?ScopeGeneratorInterface
    {
        if (!$this->currentScope) {
            $this->currentScope = $this->scopeGeneratorResolver->resolve();
        }

        return $this->currentScope;
    }
}
