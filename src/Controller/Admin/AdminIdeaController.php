<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Entity\IdeasWorkshop\Idea;
use AppBundle\Repository\IdeaRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
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
     * @Route("/{id}/contributors", name="app_admin_idea_contributors")
     * @Method("GET")
     * @Security("has_role('ROLE_APP_ADMIN_IDEAS_WORKSHOP_IDEA_ALL')")
     */
    public function membersAction(Idea $idea, IdeaRepository $repository): Response
    {
        return $this->render('admin/ideas_workshop/idea_contributors.html.twig', [
            'idea' => $idea,
            'contributors' => $contributors = $repository->getContributors($idea),
            'contributors_count' => \count($contributors),
        ]);
    }
}
