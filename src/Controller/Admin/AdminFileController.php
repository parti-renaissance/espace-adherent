<?php

namespace App\Controller\Admin;

use App\Repository\Filesystem\FileRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminFileController extends Controller
{
    /**
     * @Route("/file-directory/autocompletion",
     *     name="app_autocomplete_file_directory",
     *     condition="request.isXmlHttpRequest()",
     *     methods={"GET"}
     * )
     *
     * @Security("is_granted('ROLE_ADMIN_FILES')")
     */
    public function directoriesAutocompleteAction(Request $request, FileRepository $repository): JsonResponse
    {
        $directories = $repository->findForAutocomplete(
            mb_strtolower($request->query->get('term'))
        );

        return $this->json(
            $directories,
            Response::HTTP_OK,
            [],
            ['groups' => ['autocomplete']]
        );
    }
}
