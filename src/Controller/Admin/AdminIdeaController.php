<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Entity\IdeasWorkshop\Idea;
use AppBundle\Repository\IdeasWorkshop\IdeaRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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
    public function enableAction(Request $request, Idea $idea, EntityManagerInterface $manager): Response
    {
        if ($idea->isEnabled()) {
            $this->addFlash('warning', 'La proposition a déjà été démodéré');
        } else {
            $idea->setEnabled(true);
            $manager->flush();

            $this->addFlash('success', 'La proposition a bien été démodéré');
        }

        return $this->prepareRedirectFromRequest($request)
            ?? $this->redirectToRoute('admin_app_ideasworkshop_idea_list');
    }

    /**
     * @Route("/{uuid}/disable", name="app_admin_idea_disable", methods={"GET"})
     * @Entity("idea", expr="repository.findOneByUuid(uuid, true)")
     */
    public function disableAction(Request $request, Idea $idea, ObjectManager $manager): Response
    {
        if (!$idea->isEnabled()) {
            $this->addFlash('warning', 'La proposition a déjà été modéré');
        } else {
            $idea->setEnabled(false);
            $manager->flush();

            $this->addFlash('success', 'La proposition a bien été modéré');
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
}
