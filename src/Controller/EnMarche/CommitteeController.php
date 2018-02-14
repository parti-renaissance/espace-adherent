<?php

namespace AppBundle\Controller\EnMarche;

use AppBundle\Committee\CommitteePermissions;
use AppBundle\Committee\Feed\CommitteeMessage;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\Committee;
use AppBundle\Entity\CommitteeFeedItem;
use AppBundle\Form\CommitteeFeedMessageType;
use AppBundle\Form\DeleteType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/comites/{slug}")
 */
class CommitteeController extends Controller
{
    /**
     * @Route(name="app_committee_show")
     * @Method("GET|POST")
     * @Security("is_granted('SHOW_COMMITTEE', committee)")
     */
    public function showAction(Request $request, Committee $committee): Response
    {
        $form = null;
        if ($this->isGranted(CommitteePermissions::HOST, $committee)) {
            $message = new CommitteeMessage($this->getUser(), $committee);
            $form = $this->createForm(CommitteeFeedMessageType::class, $message)
                ->handleRequest($request)
            ;

            if ($form->isSubmitted() && $form->isValid()) {
                $this->get('app.committee.feed_manager')->createMessage($message);
                if ($message->isPublished()) {
                    $this->addFlash('info', $this->get('translator')->trans('committee.message_published'));
                } else {
                    $this->addFlash('info', $this->get('translator')->trans('committee.message_created'));
                }

                return $this->redirect($this->generateUrl('app_committee_show', ['slug' => $committee->getSlug()]));
            }
        }

        $committeeManager = $this->get('app.committee.manager');

        return $this->render('committee/show.html.twig', [
            'committee' => $committee,
            'committee_hosts' => $committeeManager->getCommitteeHosts($committee),
            'committee_timeline' => $committeeManager->getTimeline($committee, $this->getParameter('timeline_max_messages')),
            'committee_timeline_max_messages' => $this->getParameter('timeline_max_messages'),
            'form' => $form ? $form->createView() : null,
        ]);
    }

    /**
     * @Route("/timeline/{id}/edit", name="app_committee_timeline_edit")
     * @ParamConverter("committee", options={"mapping":{"slug": "slug"}})
     * @ParamConverter("committeeFeedItem", options={"mapping":{"id": "id"}})
     * @Method("GET|POST")
     * @Security("is_granted('ADMIN_FEED_COMMITTEE', committeeFeedItem)")
     */
    public function timelineEditAction(Request $request, Committee $committee, CommitteeFeedItem $committeeFeedItem): Response
    {
        $form = null;
        if ($this->isGranted(CommitteePermissions::HOST, $committee)) {
            $form = $this
                ->createForm(CommitteeFeedMessageType::class, $committeeFeedItem, ['data_class' => CommitteeFeedItem::class])
                ->handleRequest($request)
            ;

            if ($form->isSubmitted() && $form->isValid()) {
                $this->getDoctrine()->getManager()->flush();
                $this->addFlash('info', $this->get('translator')->trans('committee.message_edited'));

                return $this->redirect($this->generateUrl('app_committee_show', ['slug' => $committee->getSlug()]));
            }
        }

        $committeeManager = $this->get('app.committee.manager');

        return $this->render('committee/show.html.twig', [
            'committee' => $committee,
            'committee_hosts' => $committeeManager->getCommitteeHosts($committee),
            'committee_timeline' => $committeeManager->getTimeline($committee,
                $this->getParameter('timeline_max_messages')),
            'committee_timeline_max_messages' => $this->getParameter('timeline_max_messages'),
            'form' => $form ? $form->createView() : null,
        ]);
    }

    /**
     * @Route("/timeline/{id}/delete", name="app_committee_timeline_delete")
     * @ParamConverter("committee", options={"mapping":{"slug": "slug"}})
     * @ParamConverter("committeeFeedItem", options={"mapping":{"id": "id"}})
     * @Method("GET|DELETE")
     * @Security("is_granted('ADMIN_FEED_COMMITTEE', committeeFeedItem)")
     */
    public function timelineDeleteAction(Request $request, Committee $committee, CommitteeFeedItem $committeeFeedItem): Response
    {
        $deleteForm = $this
            ->createForm(DeleteType::class)
            ->handleRequest($request)
        ;

        if ($deleteForm->isSubmitted() && $deleteForm->isValid()) {
            $em = $this->getDoctrine()->getManager();

            $em->remove($committeeFeedItem);
            $em->flush();
            $this->addFlash('info', $this->get('translator')->trans('committee.message_deleted'));

            return $this->redirect($this->generateUrl('app_committee_show', ['slug' => $committee->getSlug()]));
        }

        $committeeManager = $this->get('app.committee.manager');

        return $this->render('committee/delete.html.twig', [
            'committee' => $committee,
            'committee_hosts' => $committeeManager->getCommitteeHosts($committee),
            'committee_feed_item' => $committeeFeedItem,
            'delete_form' => $deleteForm->createView()
        ]);
    }

    /**
     * @Route("/timeline", name="app_committee_timeline")
     * @Method("GET")
     * @Security("is_granted('SHOW_COMMITTEE', committee)")
     */
    public function timelineAction(Request $request, Committee $committee): Response
    {
        $timeline = $this->get('app.committee.manager')->getTimeline(
            $committee,
            $this->getParameter('timeline_max_messages'),
            $request->query->getInt('offset', 0)
        );

        return $this->render('committee/timeline/feed.html.twig', [
            'committee' => $committee,
            'committee_timeline' => $timeline,
            'has_role_adherent' => $this->getUser() instanceof Adherent && $this->getUser()->isAdherent(),
        ]);
    }

    /**
     * @Route("/rejoindre", name="app_committee_follow", condition="request.request.has('token')")
     * @Method("POST")
     * @Security("is_granted('FOLLOW_COMMITTEE', committee)")
     */
    public function followAction(Request $request, Committee $committee): Response
    {
        if (!$this->isCsrfTokenValid('committee.follow', $request->request->get('token'))) {
            throw $this->createAccessDeniedException('Invalid CSRF protection token to follow committee.');
        }

        $this->get('app.committee.authority')->followCommittee($this->getUser(), $committee);

        return new JsonResponse([
            'button' => [
                'label' => 'Quitter ce comité',
                'action' => 'quitter',
                'csrf_token' => (string) $this->get('security.csrf.token_manager')->getToken('committee.unfollow'),
            ],
        ]);
    }

    /**
     * @Route("/quitter", name="app_committee_unfollow", condition="request.request.has('token')")
     * @Method("POST")
     * @Security("is_granted('UNFOLLOW_COMMITTEE', committee)")
     */
    public function unfollowAction(Request $request, Committee $committee): Response
    {
        if (!$this->isCsrfTokenValid('committee.unfollow', $request->request->get('token'))) {
            throw $this->createAccessDeniedException('Invalid CSRF protection token to unfollow committee.');
        }

        $this->get('app.committee.manager')->unfollowCommittee($this->getUser(), $committee);

        return new JsonResponse([
            'button' => [
                'label' => 'Suivre ce comité',
                'action' => 'rejoindre',
                'csrf_token' => (string) $this->get('security.csrf.token_manager')->getToken('committee.follow'),
            ],
        ]);
    }
}
