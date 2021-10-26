<?php

namespace App\Controller\Api\Pap;

use App\Repository\Pap\AddressRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class AddressNearController extends AbstractController
{
    public function __invoke(Request $request, AddressRepository $addressRepository): Response
    {
        if (
            !$request->query->has('latitude')
            || !$request->query->has('longitude')
            || !$request->query->has('zoom')
        ) {
            throw new BadRequestHttpException('Some required parameters are missing. (latitude, longitude, zoom)');
        }

        $addresses = $addressRepository->findNear(
            $request->query->get('latitude'),
            $request->query->get('longitude'),
            $request->query->get('zoom')
        );

        return $this->json($addresses, Response::HTTP_OK, [], ['groups' => ['pap_address_list']]);
    }
}
