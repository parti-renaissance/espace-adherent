<?php

namespace AppBundle\Controller\EnMarche;

use AppBundle\Controller\EntityControllerTrait;
use AppBundle\Entity\MemberSummary\JobExperience;
use AppBundle\Entity\MemberSummary\Language;
use AppBundle\Entity\MemberSummary\Training;
use AppBundle\Entity\Skill;
use AppBundle\Form\JobExperienceType;
use AppBundle\Form\LanguageType;
use AppBundle\Form\SummaryType;
use AppBundle\Form\TrainingType;
use AppBundle\Membership\MemberActivityTracker;
use AppBundle\Repository\SkillRepository;
use AppBundle\Summary\SummaryManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/espace-adherent/mon-profil")
 */
class SummaryManagerController extends Controller
{
    use EntityControllerTrait;

    /**
     * @Route(name="app_summary_manager_index")
     * @Method("GET")
     */
    public function indexAction()
    {
        $member = $this->getUser();
        $manager = $this->get(SummaryManager::class);
        $summary = $manager->getForAdherent($this->getUser());
        $manager->setUrlProfilePicture($summary);

        return $this->render('summary_manager/index.html.twig', [
            'summary' => $summary,
            'recent_activities' => $this->get(MemberActivityTracker::class)->getRecentActivitiesForAdherent($member),
        ]);
    }

    /**
     * @Route("/activites_recentes/cacher_afficher", name="app_summary_manager_toggle_showing_recent_activities")
     * @Method("GET")
     */
    public function toggleShowingRecentActivitiesAction()
    {
        $manager = $this->get(SummaryManager::class);
        $summary = $manager->getForAdherent($this->getUser());

        $summary->toggleShowingRecentActivities();
        $this->get(SummaryManager::class)->updateSummary($summary);
        $this->addFlash('info', 'summary.step.success');

        return $this->redirectToRoute('app_summary_manager_index', ['slug' => $summary->getSlug()]);
    }

    /**
     * @Route("/experience/{id}", defaults={"id": ""}, name="app_summary_manager_handle_experience")
     * @Method("GET|POST")
     */
    public function handleExperienceAction(Request $request, ?JobExperience $experience)
    {
        $summaryManager = $this->get(SummaryManager::class);
        $summary = $summaryManager->getForAdherent($this->getUser());
        $form = $this->createForm(JobExperienceType::class, $experience, [
            'summary' => $summary,
            'collection' => $summary->getExperiences(),
            'add_submit_button' => false,
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $summaryManager->updateExperiences($summary, $experience ?: $form->getData());
            $this->addFlash('info', 'summary.handle_experience.success');

            return $this->redirectToRoute('app_summary_manager_index');
        }

        return $this->render('summary_manager/handle_experience.html.twig', [
            'experience_form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/experience/{id}/supprimer", name="app_summary_manager_remove_experience")
     * @Method("DELETE")
     */
    public function removeExperienceAction(Request $request, JobExperience $experience)
    {
        $form = $this->createDeleteForm('', SummaryManager::DELETE_EXPERIENCE_TOKEN, $request);

        if (!$form->isSubmitted() || !$form->isValid()) {
            throw $this->createNotFoundException($form->isValid() ? 'Invalid token.' : 'No form submitted.');
        }

        if ($this->get(SummaryManager::class)->removeExperience($this->getUser(), $experience)) {
            $this->addFlash('info', 'summary.remove_experience.success');
        }

        return $this->redirectToRoute('app_summary_manager_index');
    }

    /**
     * @Route("/formation/{id}", defaults={"id": ""}, name="app_summary_manager_handle_training")
     * @Method("GET|POST")
     */
    public function handleTrainingAction(Request $request, ?Training $training)
    {
        $summaryManager = $this->get(SummaryManager::class);
        $summary = $summaryManager->getForAdherent($this->getUser());
        $form = $this->createForm(TrainingType::class, $training, [
            'summary' => $summary,
            'collection' => $summary->getTrainings(),
            'add_submit_button' => false,
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $summaryManager->updateTrainings($summary, $training ?: $form->getData());
            $this->addFlash('info', 'summary.handle_training.success');

            return $this->redirectToRoute('app_summary_manager_index');
        }

        return $this->render('summary_manager/handle_training.html.twig', [
            'training_form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/formation/{id}/supprimer", name="app_summary_manager_remove_training")
     * @Method("DELETE")
     */
    public function removeTrainingAction(Request $request, Training $training)
    {
        $form = $this->createDeleteForm('', SummaryManager::DELETE_TRAINING_TOKEN, $request);

        if (!$form->isSubmitted() || !$form->isValid()) {
            throw $this->createNotFoundException($form->isValid() ? 'Invalid token.' : 'No form submitted.');
        }

        if ($this->get(SummaryManager::class)->removeTraining($this->getUser(), $training)) {
            $this->addFlash('info', 'summary.remove_training.success');
        }

        return $this->redirectToRoute('app_summary_manager_index');
    }

    /**
     * @Route("/langue/{id}", defaults={"id": ""}, name="app_summary_manager_handle_language")
     * @Method("GET|POST")
     */
    public function handleLanguageAction(Request $request, ?Language $language)
    {
        $form = $this->createForm(LanguageType::class, $language);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->get(SummaryManager::class)->updateLanguages($this->getUser(), $language ?: $form->getData());
            $this->addFlash('info', 'summary.handle_language.success');

            return $this->redirectToRoute('app_summary_manager_index');
        }

        return $this->render('summary_manager/handle_language.html.twig', [
            'language_form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/langue/{id}/supprimer", name="app_summary_manager_remove_language")
     * @Method("DELETE")
     */
    public function removeLanguageAction(Request $request, Language $language)
    {
        $form = $this->createDeleteForm('', SummaryManager::DELETE_LANGUAGE_TOKEN, $request);

        if (!$form->isSubmitted() || !$form->isValid()) {
            throw $this->createNotFoundException($form->isValid() ? 'Invalid token.' : 'No form submitted.');
        }

        if ($this->get(SummaryManager::class)->removeLanguage($this->getUser(), $language)) {
            $this->addFlash('info', 'summary.remove_language.success');
        }

        return $this->redirectToRoute('app_summary_manager_index');
    }

    /**
     * @Route("/publier", name="app_summary_manager_publish")
     * @Method("GET")
     */
    public function publishAction()
    {
        $manager = $this->get(SummaryManager::class);
        $summary = $manager->getForAdherent($this->getUser());

        $manager->publishSummary($summary);

        $this->addFlash('info', 'summary.published.success');

        return $this->redirectToRoute('app_summary_manager_index');
    }

    /**
     * @Route("/depublier", name="app_summary_manager_unpublish")
     * @Method("GET")
     */
    public function unpublishAction()
    {
        if ($this->get(SummaryManager::class)->unpublishSummaryForAdherent($this->getUser())) {
            $this->addFlash('info', 'summary.unpublished.success');
        } else {
            $this->addFlash('info', 'summary.unpublished.error');
        }

        return $this->redirectToRoute('app_summary_manager_index');
    }

    /**
     * @Route("/competences/autocompletion",
     *     name="app_summary_manager_skills_autocomplete",
     *     condition="request.isXmlHttpRequest()"
     * )
     * @Method("GET")
     */
    public function skillsAutocompleteAction(Request $request)
    {
        $skills = $this->getDoctrine()->getRepository(Skill::class)->findAvailableSkillsFor(
            $this->get('sonata.core.slugify.cocur')->slugify($request->query->get('term')),
            $this->getUser(), SkillRepository::FIND_FOR_SUMMARY);

        return new JsonResponse($skills);
    }

    /**
     * @Route("/{step}", name="app_summary_manager_step")
     * @Method("GET|POST")
     */
    public function stepAction(Request $request, string $step)
    {
        if (!SummaryType::stepExists($step)) {
            throw $this->createNotFoundException(sprintf('Invalid step "%s", known steps are "%s".', $step, implode('", "', SummaryType::STEPS)));
        }

        $summary = $this->get(SummaryManager::class)->getForAdherent($this->getUser());
        $form = $this->createForm(SummaryType::class, $summary, [
            'step' => $step,
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->get(SummaryManager::class)->updateSummary($summary);
            $this->addFlash('info', 'summary.step.success');

            return $this->redirectToRoute('app_summary_manager_index');
        }

        return $this->render('summary_manager/step.html.twig', [
            'summary_form' => $form->createView(),
            'step' => $step,
        ]);
    }

    /**
     * @Route("/photo/supprimer", name="app_summary_manager_remove_photo")
     * @Method("DELETE")
     */
    public function removePhotoAction(Request $request): Response
    {
        $form = $this->createDeleteForm('', SummaryManager::DELETE_PHOTO_TOKEN, $request);

        if (!$form->isValid()) {
            throw $this->createNotFoundException('Invalid token.');
        }

        $summary = $this->get(SummaryManager::class)->getForAdherent($this->getUser());

        if ($this->get(SummaryManager::class)->removePhoto($summary)) {
            $this->addFlash('info', 'summary.remove_photo.success');
        } else {
            $this->addFlash('error', 'summary.remove_photo.error');
        }

        return $this->redirectToRoute('app_summary_manager_index');
    }
}
