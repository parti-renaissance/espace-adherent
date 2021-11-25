<?php

namespace App\Controller\Api\Pap;

use App\Repository\Pap\AddressRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class AddressNearController
{
    private const MAX_LIMIT = 300;

    public function __invoke(Request $request, AddressRepository $addressRepository): array
    {
        if (
            !$request->query->has('latitude')
            || !$request->query->has('longitude')
            || !$request->query->has('zoom')
        ) {
            throw new BadRequestHttpException('Some required parameters are missing. (latitude, longitude, zoom)');
        }

        $limit = $request->query->getInt('limit', self::MAX_LIMIT);

        return $addressRepository->findNear(
            $request->query->filter('latitude', null, \FILTER_VALIDATE_FLOAT),
            $request->query->filter('longitude', null, \FILTER_VALIDATE_FLOAT),
            $request->query->getInt('zoom'),
            $limit > self::MAX_LIMIT ? self::MAX_LIMIT : $limit
        );
    }
}
