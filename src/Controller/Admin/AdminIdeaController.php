<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Entity\IdeasWorkshop\Idea;
use AppBundle\IdeasWorkshop\Events;
use AppBundle\Repository\IdeasWorkshop\IdeaRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/ideasworkshop-idea")
 * @Security("has_role('ROLE_APP_ADMIN_IDEAS_WORKSHOP_IDEA_ALL')")
 */
class AdminIdeaController extends Controller
{
    use RedirectToTargetTrait;

    /**
     * @Route("/{id}/contribute", name="app_admin_idea_contribute", methods={"GET"})
     */
    public function contributeAction(Idea $idea, ObjectManager $manager): Response
    {
        if (!$idea->isFinalized()) {
            $this->addFlash('warning', 'La proposition a déjà été remise en contribution');
        } else {
            $idea->publish();
            $manager->flush();

            $this->addFlash('success', 'La proposition a bien été remise en contribution');
        }

        return $this->redirectToRoute('admin_app_ideasworkshop_idea_list');
    }

    /**
     * @Route("/{id}/contributors", name="app_admin_idea_contributors", methods={"GET"})
     */
    public function membersAction(Idea $idea, IdeaRepository $repository): Response
    {
        return $this->render('admin/ideas_workshop/idea/idea_contributors.html.twig', [
            'idea' => $idea,
            'contributors' => $contributors = $repository->getContributors($idea),
            'contributors_count' => \count($contributors),
        ]);
    }

    /**
     * @Route("/{uuid}/enable", name="app_admin_idea_enable", methods={"GET"})
     * @Entity("idea", expr="repository.findOneByUuid(uuid, true)")
     */
    public function enableAction(
        Request $request,
        Idea $idea,
        EntityManagerInterface $manager,
        EventDispatcherInterface $dispatcher
    ): Response {
        if ($idea->isEnabled()) {
            $this->addFlash('warning', 'La proposition a déjà été démodérée.');
        } else {
            $idea->setEnabled(true);
            $dispatcher->dispatch(Events::IDEA_ENABLE, new GenericEvent($idea));
            $manager->flush();

            $this->addFlash('success', 'La proposition a bien été démodérée.');
        }

        return $this->prepareRedirectFromRequest($request)
            ?? $this->redirectToRoute('admin_app_ideasworkshop_idea_list');
    }

    /**
     * @Route("/{uuid}/disable", name="app_admin_idea_disable", methods={"GET"})
     * @Entity("idea", expr="repository.findOneByUuid(uuid, true)")
     */
    public function disableAction(
        Request $request,
        Idea $idea,
        ObjectManager $manager,
        EventDispatcherInterface $dispatcher
    ): Response {
        if (!$idea->isEnabled()) {
            $this->addFlash('warning', 'La proposition a déjà été modérée.');
        } else {
            $idea->setEnabled(false);
            $dispatcher->dispatch(Events::IDEA_DISABLE, new GenericEvent($idea));
            $manager->flush();

            $this->addFlash('success', 'La proposition a bien été modérée.');
        }

        return $this->prepareRedirectFromRequest($request)
            ?? $this->redirectToRoute('admin_app_ideasworkshop_idea_list');
    }

    /**
     * @Route("/{id}/finalize", name="app_admin_idea_finalize", methods={"GET"})
     */
    public function finalizeAction(Idea $idea, ObjectManager $manager): Response
    {
        if ($idea->isFinalized()) {
            $this->addFlash('warning', 'La proposition a déjà été finalisé');
        } else {
            $idea->setPublishedAt(new \DateTime());
            $idea->finalize();

            $manager->flush();

            $this->addFlash('success', 'La proposition a bien été finalisé');
        }

        return $this->redirectToRoute('admin_app_ideasworkshop_idea_list');
    }

    /**
     * @Route("/{id}/extend", name="app_admin_idea_extend", methods={"GET"})
     */
    public function extendAction(Idea $idea, ObjectManager $manager): Response
    {
        if ($idea->isDraft()) {
            $this->addFlash('warning', 'La proposition est un brouillon');
        } else {
            $idea->publish();

            $manager->flush();

            $this->addFlash('success', 'La proposition a bien été prolongée');
        }

        return $this->redirectToRoute('admin_app_ideasworkshop_idea_list');
    }
}
