<?php

namespace App\Normalizer;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Paginator;
use ApiPlatform\Core\DataProvider\PaginatorInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ItemCollectionNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    private const FORMAT = 'json';

    public function normalize($data, $format = null, array $context = [])
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
                'total_items' => $data->getTotalItems(),
                'items_per_page' => $data->getItemsPerPage(),
                'count' => $data->count(),
                'current_page' => $data->getCurrentPage(),
                'last_page' => $data->getLastPage(),
            ],
            'items' => $items,
        ];
    }

    public function supportsNormalization($data, $format = null, array $context = [])
    {
        return self::FORMAT === $format && $data instanceof PaginatorInterface;
    }
}
