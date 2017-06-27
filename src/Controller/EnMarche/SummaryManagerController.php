<?php

namespace AppBundle\Controller\EnMarche;

use AppBundle\Controller\CanaryControllerTrait;
use AppBundle\Controller\EntityControllerTrait;
use AppBundle\Entity\MemberSummary\JobExperience;
use AppBundle\Entity\MemberSummary\Training;
use AppBundle\Form\JobExperienceType;
use AppBundle\Form\TrainingType;
use AppBundle\Summary\SummaryManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/espace-adherent/mon-cv")
 */
class SummaryManagerController extends Controller
{
    use CanaryControllerTrait;
    use EntityControllerTrait;

    /**
     * @Route(name="app_summary_manager_index")
     * @Method("GET")
     */
    public function indexAction()
    {
        $this->disableInProduction();

        return $this->render('summary_manager/index.html.twig', [
            'summary' => $this->get(SummaryManager::class)->getForAdherent($this->getUser()),
            'recent_activities' => [], // TODO $this->get(MembershipTracker::class)->getRecentActivitiesForAdherent($this->getUser()),
        ]);
    }

    /**
     * @Route("/experience/{id}", defaults={"id": ""}, name="app_summary_manager_handle_experience")
     * @Method("GET|POST")
     */
    public function handleExperienceAction(Request $request, ?JobExperience $experience)
    {
        $this->disableInProduction();

        $summaryManager = $this->get(SummaryManager::class);
        $summary = $summaryManager->getForAdherent($this->getUser());
        $form = $this->createForm(JobExperienceType::class, $experience, [
            'summary' => $summary,
            'collection' => $summary->getExperiences(),
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $summaryManager->updateExperiences($summary, $experience ?: $form->getData());
            $this->addFlash('info', $this->get('translator')->trans('summary.handle_experience.success'));

            return $this->redirectToRoute('app_summary_manager_index');
        }

        $deleteForm = $experience ? $this->createDeleteForm(
            $this->generateUrl('app_summary_manager_remove_experience', ['id' => $experience->getId()]),
            SummaryManager::DELETE_EXPERIENCE_TOKEN
        )->createView() : null;

        return $this->render('summary_manager/handle_experience.html.twig', [
            'experience_form' => $form->createView(),
            'delete_form' => $deleteForm,
        ]);
    }

    /**
     * @Route("/experience/{id}/supprimer", name="app_summary_manager_remove_experience")
     * @Method("DELETE")
     */
    public function removeExperienceAction(Request $request, JobExperience $experience)
    {
        $this->disableInProduction();

        $form = $this->createDeleteForm('', SummaryManager::DELETE_EXPERIENCE_TOKEN, $request);

        if (!$form->isSubmitted() || !$form->isValid()) {
            throw $this->createNotFoundException($form->isValid() ? 'Invalid token.' : 'No form submitted.');
        }

        if ($this->get(SummaryManager::class)->removeExperience($this->getUser(), $experience)) {
            $this->addFlash('info', $this->get('translator')->trans('summary.remove_experience.success'));
        }

        return $this->redirectToRoute('app_summary_manager_index');
    }

    /**
     * @Route("/formation/{id}", defaults={"id": ""}, name="app_summary_manager_handle_training")
     * @Method("GET|POST")
     */
    public function handleTrainingAction(Request $request, ?Training $training)
    {
        $this->disableInProduction();

        $summaryManager = $this->get(SummaryManager::class);
        $summary = $summaryManager->getForAdherent($this->getUser());
        $form = $this->createForm(TrainingType::class, $training, [
            'summary' => $summary,
            'collection' => $summary->getTrainings(),
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $summaryManager->updateTrainings($summary, $training ?: $form->getData());
            $this->addFlash('info', $this->get('translator')->trans('summary.handle_training.success'));

            return $this->redirectToRoute('app_summary_manager_index');
        }

        $deleteForm = $training ? $this->createDeleteForm(
            $this->generateUrl('app_summary_manager_remove_training', ['id' => $training->getId()]),
            SummaryManager::DELETE_TRAINING_TOKEN
        )->createView() : null;

        return $this->render('summary_manager/handle_training.html.twig', [
            'training_form' => $form->createView(),
            'delete_form' => $deleteForm,
        ]);
    }

    /**
     * @Route("/formation/{id}/supprimer", name="app_summary_manager_remove_training")
     * @Method("DELETE")
     */
    public function removeTrainingAction(Request $request, Training $training)
    {
        $this->disableInProduction();

        $form = $this->createDeleteForm('', SummaryManager::DELETE_TRAINING_TOKEN, $request);

        if (!$form->isSubmitted() || !$form->isValid()) {
            throw $this->createNotFoundException($form->isValid() ? 'Invalid token.' : 'No form submitted.');
        }

        if ($this->get(SummaryManager::class)->removeTraining($this->getUser(), $training)) {
            $this->addFlash('info', $this->get('translator')->trans('summary.remove_training.success'));
        }

        return $this->redirectToRoute('app_summary_manager_index');
    }
}
