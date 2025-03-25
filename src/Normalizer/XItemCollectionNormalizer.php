<?php

namespace App\Normalizer;

use ApiPlatform\Doctrine\Orm\Paginator;
use ApiPlatform\State\Pagination\PaginatorInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class XItemCollectionNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    private const FORMAT = 'json';

    public function normalize($data, $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        /*
         * Fix: for blocking iri generation from custom routes
         * ex:
         * `/resources_A/{resource_A_id}/sub_resources_B/{sub_resource_B_id}`
         * this route has two identifiers `resource_A_id` and `sub_resource_B_id` and
         * IriGenerator of ApiPlatform cannot generate the URL of final resource with this type of route
         */
        $context['iri'] = true;

        $items = [];

        /** @var Paginator $data */
        foreach ($data as $object) {
            $items[] = $this->normalizer->normalize($object, $format, $context);
        }

        return [
            'metadata' => [
                'total_items' => (int) $data->getTotalItems(),
                'items_per_page' => (int) $data->getItemsPerPage(),
                'count' => $data->count(),
                'current_page' => (int) $data->getCurrentPage(),
                'last_page' => (int) $data->getLastPage(),
            ],
            'items' => $items,
        ];
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            PaginatorInterface::class => true,
        ];
    }

    public function supportsNormalization($data, $format = null, array $context = []): bool
    {
        return self::FORMAT === $format && $data instanceof PaginatorInterface;
    }
}
