<?php

namespace App\Normalizer;

use App\Entity\EmailTemplate\EmailTemplate;
use App\Scope\ScopeGeneratorResolver;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class JeMengageWebEmailTemplateDenormalizer implements DenormalizerInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;

    private const ALREADY_CALLED = 'JE_MENGAGE_WEB_EMAIL_TEMPLATE_DENORMALIZER_ALREADY_CALLED';

    public function __construct(
        private readonly Security $security,
        private readonly ScopeGeneratorResolver $scopeGeneratorResolver
    ) {
    }

    public function denormalize($data, string $class, string $format = null, array $context = [])
    {
        $context[self::ALREADY_CALLED] = true;

        $scope = $this->scopeGeneratorResolver->generate();

        /** @var EmailTemplate $template */
        $template = $this->denormalizer->denormalize($data, $class, $format, $context);

        $template->setScopes([$scope->getMainCode()]);

        foreach ($scope->getZones() as $zone) {
            $template->addZone($zone);
        }

        return $template;
    }

    public function supportsDenormalization($data, string $type, string $format = null, array $context = []): bool
    {
        return !isset($context[self::ALREADY_CALLED])
            && EmailTemplate::class === $type
            && 'api_email_templates_post_collection' === ($context['operation_name'] ?? null);
    }
}
