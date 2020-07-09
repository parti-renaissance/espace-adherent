<?php

namespace App\Normalizer;

use App\Entity\ElectedRepresentative\ElectedRepresentative;
use App\Mailchimp\Synchronisation\ElectedRepresentativeTagsBuilder;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ElectedRepresentativeNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    private const ALREADY_CALLED = 'ElECTED_REPRESENTATIVE_NORMALIZER_ALREADY_CALLED';

    private $tagsBuilder;

    public function __construct(ElectedRepresentativeTagsBuilder $tagsBuilder)
    {
        $this->tagsBuilder = $tagsBuilder;
    }

    public function normalize($object, $format = null, array $context = [])
    {
        $context[self::ALREADY_CALLED] = true;

        $data = $this->normalizer->normalize($object, $format, $context);

        if (\in_array('elected_representative_change_diff', $context['groups'])) {
            $data['activeTagCodes'] = $this->tagsBuilder->buildTags($object);
        }

        return $data;
    }

    public function supportsNormalization($data, $format = null, $context = [])
    {
        return !isset($context[self::ALREADY_CALLED]) && $data instanceof ElectedRepresentative;
    }
}
