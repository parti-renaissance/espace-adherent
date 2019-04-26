<?php

namespace AppBundle\Controller\EnMarche\Coordinator;

use AppBundle\Coordinator\Filter\CommitteeFilter;
use AppBundle\Entity\Committee;
use AppBundle\Exception\BaseGroupException;
use AppBundle\Form\CoordinatorAreaType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/espace-coordinateur/comites")
 * @Security("has_role('ROLE_COORDINATOR_COMMITTEE')")
 */
class CoordinatorCommitteeController extends Controller
{
    /**
     * @Route("/list", name="app_coordinator_committees")
     * @Method("GET")
     */
    public function committeesAction(Request $request): Response
    {
        try {
            $filter = CommitteeFilter::fromQueryString($request);
        } catch (\UnexpectedValueException $e) {
            throw new BadRequestHttpException('Unexpected committee request status in the query string.', $e);
        }

        $committees = $this->get('app.committee.manager')->getCoordinatorCommittees($this->getUser(), $filter);

        $forms = [];
        foreach ($committees as $committee) {
            $form = $this->createForm(CoordinatorAreaType::class, $committee, [
                'data_class' => Committee::class,
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
            'filter' => $filter,
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
        $form = $this->createForm(CoordinatorAreaType::class, $committee, [
            'data_class' => Committee::class,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                if ($form->get('refuse')->isClicked()) {
                    $this->get('app.committee.authority')->preRefuse($committee);
                    $this->addFlash('info', 'Merci. Votre appréciation a été transmise à nos équipes.');
                } elseif ($form->get('accept')->isClicked()) {
                    $this->get('app.committee.authority')->preApprove($committee);
                    $this->addFlash('info', 'Merci. Votre appréciation a été transmise à nos équipes.');
                }
            } catch (BaseGroupException $exception) {
                $this->addFlash('info', sprintf('Le comité #%d a déjà été traité par un administrateur', $committee->getId()));
            }
        } else {
            foreach ($form->getErrors(true) as $error) {
                $this->addFlash('error_'.$form->getData()->getId(), $error->getMessage());
            }
        }

        return $this->redirectToRoute('app_coordinator_committees');
    }
}
