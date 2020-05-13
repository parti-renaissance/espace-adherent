<?php

namespace App\Controller\EnMarche;

use App\Entity\CitizenProject;
use App\Form\CitizenProjectImageType;
use App\Form\CitizenProjectTractType;
use App\MediaGenerator\Image\CitizenProjectCoverGenerator;
use App\MediaGenerator\Pdf\CitizenProjectTractGenerator;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/projets-citoyens/{slug}/media-generateur")
 *
 * @Security("is_granted('ADMINISTRATE_CITIZEN_PROJECT', citizenProject)")
 */
class CitizenProjectMediaGeneratorController extends Controller
{
    /**
     * @Route("/images", name="app_citizen_project_image_generator", methods={"GET", "POST"})
     */
    public function generateImageAction(Request $request, CitizenProject $citizenProject): Response
    {
        $form = $this
            ->createForm(CitizenProjectImageType::class)
            ->handleRequest($request)
        ;

        $coverImage = null;

        if ($form->isSubmitted() && $form->isValid()) {
            $coverImage = $this->get(CitizenProjectCoverGenerator::class)->generate($form->getData());
        }

        return $this->render(
            'citizen_project/media_generator_image_form.html.twig',
            [
                'form' => $form->createView(),
                'previewCoverImage' => $coverImage ? $coverImage->getContentAsDataUrl() : null,
                'citizen_project' => $citizenProject,
            ]
        );
    }

    /**
     * @Route("/tracts", name="app_citizen_project_tract_generator", methods={"GET", "POST"})
     */
    public function generateTractAction(Request $request, CitizenProject $citizenProject): Response
    {
        $form = $this
            ->createForm(CitizenProjectTractType::class)
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            $tractCommand = $form->getData();

            $pdfContent = $this->get(CitizenProjectTractGenerator::class)->generate($tractCommand);

            return new PdfResponse($pdfContent->getContent(), uniqid('tract_', false).'.pdf');
        }

        return $this->render(
            'citizen_project/media_generator_tract_form.html.twig',
            [
                'form' => $form->createView(),
                'citizen_project' => $citizenProject,
            ]
        );
    }
}
