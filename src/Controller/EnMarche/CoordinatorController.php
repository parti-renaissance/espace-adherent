<?php

namespace AppBundle\Controller\EnMarche;

use AppBundle\Entity\Committee;
use AppBundle\Exception\BaseGroupException;
use AppBundle\Committee\Filter\CommitteeFilters;
use AppBundle\Form\CoordinatorCommitteeType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * @Route("/espace-coordinateur")
 * @Security("is_granted('ROLE_COORDINATOR')")
 */
class CoordinatorController extends Controller
{
    /**
     * @Route("/comites", name="app_coordinator_committees")
     * @Method("GET")
     */
    public function committeesAction(Request $request): Response
    {
        try {
            $filters = CommitteeFilters::fromQueryString($request);
        } catch (\UnexpectedValueException $e) {
            throw new BadRequestHttpException('Unexpected committee request status in the query string.', $e);
        }

        $committees = $this->get('app.committee.manager')->getCoordinatorCommittees($this->getUser(), $filters);

        $forms = [];
        foreach ($committees as $committee) {
            $form = $this->createForm(CoordinatorCommitteeType::class, $committee, [
                'action' => $this->generateUrl('app_coordinator_committee_validate', [
                    'uuid' => $committee->getUuid(),
                    'slug' => $committee->getSlug(),
                ]),
            ]);
            $forms[$committee->getId()] = $form->createView();
        }

        return $this->render('coordinator/committees.html.twig', [
            'results' => $committees,
            'forms' => $forms,
            'filters' => $filters,
        ]);
    }

    /**
     * Pre-approves or pre-refuses a committee.
     *
     * @Route("/{uuid}/{slug}/pre-valider-comite", name="app_coordinator_committee_validate")
     * @Method("POST")
     */
    public function validateAction(Request $request, Committee $committee): Response
    {
        $form = $this->createForm(CoordinatorCommitteeType::class, $committee);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                if ($form->get('refuse')->isClicked()) {
                    $this->get('app.committee.authority')->preRefuse($committee);
                    $this->addFlash('info', sprintf('Merci. Votre appréciation a été transmise à nos équipes.', $committee->getName()));
                }

                if ($form->get('accept')->isClicked()) {
                    $this->get('app.committee.authority')->preApprove($committee);
                    $this->addFlash('info', sprintf('Merci. Votre appréciation a été transmise à nos équipes.', $committee->getName()));
                }
            } catch (BaseGroupException $exception) {
                throw $this->createNotFoundException(sprintf('Committee %u has already been treated by an administrator.', $committee->getId()), $exception);
            }
        } else {
            foreach ($form->getErrors(true) as $error) {
                $this->addFlash('error_'.$form->getData()->getId(), $error->getMessage());
            }
        }

        return $this->redirectToRoute('app_coordinator_committees');
    }
}
