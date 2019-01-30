<?php

namespace AppBundle\Normalizer;

use AppBundle\Entity\Adherent;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class AuthorNormalizer implements NormalizerInterface
{
    private $normalizer;
    private $security;

    public function __construct(NormalizerInterface $normalizer, Security $security)
    {
        $this->normalizer = $normalizer;
        $this->security = $security;
    }

    /**
     * @param Adherent $object
     */
    public function normalize($object, $format = null, array $context = [])
    {
        $data = $this->normalizer->normalize($object, $format, $context);

        if ($object->getNickname()) {
            $data['first_name'] = null;
            $data['last_name'] = null;
        } elseif (!$this->security->getUser()) {
            $data['last_name'] = $object->getLastNameInitial();
        }

        return $data;
    }

    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof Adherent;
    }
}
