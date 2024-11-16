<?php

namespace App\Controller\EnMarche\Coordinator;

use App\Committee\CommitteeManagementAuthority;
use App\Committee\CommitteeManager;
use App\Coordinator\Filter\CommitteeFilter;
use App\Entity\Committee;
use App\Exception\BaseGroupException;
use App\Form\CoordinatorAreaType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_REGIONAL_COORDINATOR')]
#[Route(path: '/espace-coordinateur/comites')]
class CoordinatorCommitteeController extends AbstractController
{
    #[Route(path: '/list', name: 'app_coordinator_committees', methods: ['GET'])]
    public function committeesAction(Request $request, CommitteeManager $manager): Response
    {
        try {
            $filter = CommitteeFilter::fromQueryString($request);
        } catch (\UnexpectedValueException $e) {
            throw new BadRequestHttpException('Unexpected committee request status in the query string.', $e);
        }

        $committees = $manager->getCoordinatorCommittees($this->getUser(), $filter);

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
     */
    #[Route(path: '/{uuid}/{slug}/pre-valider-comite', name: 'app_coordinator_committee_validate', methods: ['POST'])]
    public function validateAction(
        Request $request,
        Committee $committee,
        CommitteeManagementAuthority $committeeManagementAuthority,
    ): Response {
        $form = $this->createForm(CoordinatorAreaType::class, $committee, [
            'data_class' => Committee::class,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                if ($form->get('refuse')->isClicked()) {
                    $committeeManagementAuthority->preRefuse($committee);
                    $this->addFlash('info', 'Merci. Votre appréciation a été transmise à nos équipes.');
                } elseif ($form->get('accept')->isClicked()) {
                    $committeeManagementAuthority->preApprove($committee);
                    $this->addFlash('info', 'Merci. Votre appréciation a été transmise à nos équipes.');
                }
            } catch (BaseGroupException $exception) {
                $this->addFlash('info', \sprintf('Le comité #%d a déjà été traité par un administrateur', $committee->getId()));
            }
        } else {
            foreach ($form->getErrors(true) as $error) {
                $this->addFlash('error_'.$form->getData()->getId(), $error->getMessage());
            }
        }

        return $this->redirectToRoute('app_coordinator_committees');
    }
}
