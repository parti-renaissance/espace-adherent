<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Entity\IdeasWorkshop\Idea;
use AppBundle\Repository\IdeaRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/ideasworkshop-idea")
 */
class AdminIdeaController extends Controller
{
    /**
     * @Route("/{id}/contribute", name="app_admin_idea_contribute", methods={"GET"})
     * @Security("has_role('ROLE_APP_ADMIN_IDEAS_WORKSHOP_IDEA_ALL')")
     */
    public function contributeAction(Idea $idea, ObjectManager $manager): Response
    {
        if (!$idea->isFinalized()) {
            $this->addFlash('warning', 'L\'idée a déjà été remise en contribution');
        } else {
            $idea->publish();
            $manager->flush();

            $this->addFlash('success', 'L\'idée a bien été remise en contribution');
        }

        return $this->redirectToRoute('admin_app_ideasworkshop_idea_list');
    }

    /**
     * @Route("/{id}/contributors", name="app_admin_idea_contributors", methods={"GET"})
     * @Security("has_role('ROLE_APP_ADMIN_IDEAS_WORKSHOP_IDEA_ALL')")
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
     * @Route("/{id}/enable", name="app_admin_idea_enable", methods={"GET"})
     * @Security("has_role('ROLE_APP_ADMIN_IDEAS_WORKSHOP_IDEA_ALL')")
     */
    public function enableAction(int $id, EntityManagerInterface $manager): Response
    {
        if ($manager->getFilters()->isEnabled('enabled')) {
            $manager->getFilters()->disable('enabled');
        }

        if (!$idea = $manager->getRepository(Idea::class)->find($id)) {
            throw $this->createNotFoundException('L\'idée n\'existe pas');
        }

        if ($idea->isEnabled()) {
            $this->addFlash('warning', 'L\'idée a déjà été démodéré');
        } else {
            $idea->setEnabled(true);
            $manager->flush();

            $this->addFlash('success', 'L\'idée a bien été démodéré');
        }

        return $this->redirectToRoute('admin_app_ideasworkshop_idea_list');
    }

    /**
     * @Route("/{id}/disable", name="app_admin_idea_disable", methods={"GET"})
     * @Security("has_role('ROLE_APP_ADMIN_IDEAS_WORKSHOP_IDEA_ALL')")
     */
    public function disableAction(Idea $idea, ObjectManager $manager): Response
    {
        if (!$idea->isEnabled()) {
            $this->addFlash('warning', 'L\'idée a déjà été modéré');
        } else {
            $idea->setEnabled(false);
            $manager->flush();

            $this->addFlash('success', 'L\'idée a bien été modéré');
        }

        return $this->redirectToRoute('admin_app_ideasworkshop_idea_list');
    }

    /**
     * @Route("/{id}/finalize", name="app_admin_idea_finalize", methods={"GET"})
     * @Security("has_role('ROLE_APP_ADMIN_IDEAS_WORKSHOP_IDEA_ALL')")
     */
    public function finalizeAction(Idea $idea, ObjectManager $manager): Response
    {
        if ($idea->isFinalized()) {
            $this->addFlash('warning', 'L\'idée a déjà été finalisé');
        } else {
            $idea->setPublishedAt(new \DateTime());
            $idea->finalize();

            $manager->flush();

            $this->addFlash('success', 'L\'idée a bien été finalisé');
        }

        return $this->redirectToRoute('admin_app_ideasworkshop_idea_list');
    }
}
