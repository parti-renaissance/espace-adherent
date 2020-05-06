<?php

namespace App\Normalizer;

use App\Entity\Adherent;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class IdeaAuthorNicknameNormalizer implements NormalizerInterface
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
            if ($object->isNicknameUsed()) {
                $data['first_name'] = null;
                $data['last_name'] = null;
            } else {
                $data['nickname'] = null;
            }
        } elseif (!$this->security->getUser()) {
            $data['last_name'] = $object->getLastNameInitial();
        }

        return $data;
    }

    public function supportsNormalization($data, $format = null, array $context = [])
    {
        return $data instanceof Adherent
            && isset($context['groups'])
            && array_filter($context['groups'], static function (string $group) {
                return 0 === strpos($group, 'idea_');
            })
        ;
    }
}
