<?php

namespace AppBundle\Normalizer;

use AppBundle\Entity\Adherent;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class AuthorNormalizer implements NormalizerInterface
{
    private $normalizer;
    private $authorizationChecker;

    public function __construct(NormalizerInterface $normalizer, AuthorizationChecker $authorizationChecker)
    {
        $this->normalizer = $normalizer;
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * @param Adherent $object
     */
    public function normalize($object, $format = null, array $context = [])
    {
        $data = $this->normalizer->normalize($object, $format, $context);

        if ($this->authorizationChecker->isGranted('IS_AUTHENTICATED_ANONYMOUSLY')) {
            $data['last_name'] = $object->getLastNameInitial();
        }

        return $data;
    }

    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof Adherent;
    }
}
