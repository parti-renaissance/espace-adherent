<?php

declare(strict_types=1);

namespace App\Controller\Api\Zone;

use App\Controller\EnMarche\AccessDelegatorTrait;
use App\Geo\Http\ZoneAutocompleteFilter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

abstract class AbstractZoneAutocompleteController extends AbstractController
{
    use AccessDelegatorTrait;

    public const QUERY_SEARCH_PARAM = 'q';
    public const QUERY_ZONE_TYPE_PARAM = 'types';

    public function __construct(private readonly DenormalizerInterface $denormalizer)
    {
    }

    protected function getFilter(Request $request): ZoneAutocompleteFilter
    {
        return $this->denormalizer->denormalize($request->query->all(), ZoneAutocompleteFilter::class, null, [
            AbstractNormalizer::GROUPS => ['filter_write'],
            AbstractObjectNormalizer::DISABLE_TYPE_ENFORCEMENT => true,
        ]);
    }
}
