<?php

namespace App\Controller\Api\Jecoute;

use App\Repository\Geo\DepartmentRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * @Route("/jecoute/departments")
 * @Security("is_granted('ROLE_OAUTH_SCOPE_JEMARCHE_APP')")
 */
class DepartmentController extends Controller
{
    /**
     * @Route(
     *     "/{postalCode}",
     *     requirements={"postalCode": "\d{5}"},
     *     name="api_jecoute_departments_find",
     *     methods="GET"
     * )
     */
    public function find(
        string $postalCode,
        SerializerInterface $serializer,
        DepartmentRepository $departmentRepository
    ): Response {
        $department = $departmentRepository->findOneForJecoute($postalCode);

        if (!$department) {
            throw $this->createNotFoundException(sprintf('No department found for postal code "%s"', $postalCode));
        }

        return new JsonResponse(
            $serializer->serialize($department, 'json', ['groups' => ['department_read']]),
            JsonResponse::HTTP_OK,
            [],
            true
        );
    }
}
