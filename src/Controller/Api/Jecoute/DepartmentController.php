<?php

namespace App\Controller\Api\Jecoute;

use App\Repository\Geo\DepartmentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\SerializerInterface;

#[IsGranted('ROLE_OAUTH_SCOPE_JEMARCHE_APP')]
#[Route(path: '/jecoute/departments')]
class DepartmentController extends AbstractController
{
    #[Route(path: '/{postalCode}', requirements: ['postalCode' => '\d{5}'], name: 'api_jecoute_departments_find', methods: 'GET')]
    public function find(
        string $postalCode,
        SerializerInterface $serializer,
        DepartmentRepository $departmentRepository,
    ): Response {
        $department = $departmentRepository->findOneForJecoute($postalCode);

        if (!$department) {
            throw $this->createNotFoundException(\sprintf('No department found for postal code "%s"', $postalCode));
        }

        return new JsonResponse(
            $serializer->serialize($department, 'json', [
                'groups' => [
                    'department_read',
                    'jecoute_department_read',
                ],
                'postal_code' => $postalCode,
            ]),
            JsonResponse::HTTP_OK,
            [],
            true
        );
    }
}
