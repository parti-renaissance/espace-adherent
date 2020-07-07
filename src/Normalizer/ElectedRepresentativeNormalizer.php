<?php

namespace App\Normalizer;

use App\Entity\Adherent;
use App\Entity\ElectedRepresentative\ElectedRepresentative;
use App\Entity\IdeasWorkshop\Idea;
use App\Mailchimp\Synchronisation\ElectedRepresentativeTagsBuilder;
use App\Repository\IdeasWorkshop\IdeaRepository;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ElectedRepresentativeNormalizer implements NormalizerInterface
{
    private $normalizer;
    private $tagsBuilder;

    public function __construct(
        NormalizerInterface $normalizer,
        ElectedRepresentativeTagsBuilder $tagsBuilder
    ) {
        $this->normalizer = $normalizer;
        $this->tagsBuilder = $tagsBuilder;
    }

    public function normalize($object, $format = null, array $context = [])
    {
        $data = $this->normalizer->normalize($object, $format, $context);

        if (\in_array('elected_representative_change_diff', $context['groups'])) {
            $data['activeTagCodes'] = $this->tagsBuilder->buildTags($object);
        }

        return $data;
    }

    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof ElectedRepresentative;
    }
}
