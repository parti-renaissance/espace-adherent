<?php

namespace App\Normalizer;

use App\Entity\AuthorInstanceInterface;
use App\Scope\ScopeGeneratorResolver;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class AuthorNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    private const ALREADY_CALLED = 'AUTHOR_NORMALIZER_ALREADY_CALLED';

    public function __construct(
        private readonly Security $security,
        private readonly ScopeGeneratorResolver $scopeGeneratorResolver,
    ) {
    }

    /** @var AuthorInstanceInterface */
    public function normalize($object, ?string $format = null, array $context = [])
    {
        $context[self::ALREADY_CALLED] = true;

        $data = $this->normalizer->normalize($object, $format, $context);

        foreach (['author_role', 'author_instance', 'author_zone'] as $key) {
            if (\array_key_exists($key, $data)) {
                if (\array_key_exists('author', $data)) {
                    $authorKey = 'author';
                } elseif (\array_key_exists('organizer', $data)) {
                    $authorKey = 'organizer';
                }

                if (!empty($authorKey) && !empty($data[$authorKey])) {
                    $data[$authorKey][explode('_', $key, 2)[1]] = $data[$key];
                }

                unset($data[$key]);
            }
        }

        return $data;
    }

    public function supportsNormalization($data, $format = null, array $context = []): bool
    {
        if (isset($context[self::ALREADY_CALLED])) {
            return false;
        }

        return $data instanceof AuthorInstanceInterface;
    }
}
