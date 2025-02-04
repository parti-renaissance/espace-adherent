<?php

namespace App\Normalizer;

use App\Api\Serializer\PrivatePublicContextBuilder;
use App\Entity\AuthorInstanceInterface;
use App\Entity\Event\Event;
use App\Scope\ScopeGeneratorResolver;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class AuthorNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    public function __construct(
        private readonly Security $security,
        private readonly ScopeGeneratorResolver $scopeGeneratorResolver,
    ) {
    }

    /** @param AuthorInstanceInterface $object */
    public function normalize($object, ?string $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        $data = $this->normalizer->normalize($object, $format, $context + [__CLASS__ => true]);

        $apiContext = $context[PrivatePublicContextBuilder::CONTEXT_KEY] ?? null;

        if (\array_key_exists('author', $data)) {
            $authorKey = 'author';
        } elseif (\array_key_exists('organizer', $data)) {
            $authorKey = 'organizer';
        }

        if (empty($authorKey)) {
            return $data;
        }

        foreach (['author_scope', 'author_role', 'author_instance', 'author_zone'] as $key) {
            if (\array_key_exists($key, $data)) {
                if (!empty($data[$authorKey])) {
                    $data[$authorKey][explode('_', $key, 2)[1]] = $data[$key];

                    if (PrivatePublicContextBuilder::CONTEXT_PUBLIC_ANONYMOUS === $apiContext && !empty($data[$authorKey]['last_name'])) {
                        $data[$authorKey]['last_name'] = mb_substr($data[$authorKey]['last_name'], 0, 1);
                    }
                }

                unset($data[$key]);
            }
        }

        if ($object instanceof Event && $object->national) {
            $data[$authorKey] = null;
        }

        return $data;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            '*' => null,
            AuthorInstanceInterface::class => false,
        ];
    }

    public function supportsNormalization($data, $format = null, array $context = []): bool
    {
        return !isset($context[__CLASS__]) && $data instanceof AuthorInstanceInterface;
    }
}
