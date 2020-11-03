<?php

namespace App\Controller\EnMarche\ThematicCommunity;

use App\Controller\CanaryControllerTrait;
use App\Entity\ThematicCommunity\AdherentMembership;
use App\Entity\ThematicCommunity\Contact;
use App\Entity\ThematicCommunity\ContactMembership;
use App\Entity\ThematicCommunity\ThematicCommunity;
use App\Entity\ThematicCommunity\ThematicCommunityMembership;
use App\Form\ThematicCommunity\ThematicCommunityMembershipType;
use App\Repository\ThematicCommunity\ThematicCommunityMembershipRepository;
use App\Repository\ThematicCommunity\ThematicCommunityRepository;
use App\Security\Http\Session\AnonymousFollowerSession;
use App\ThematicCommunity\Handler\ThematicCommunityMembershipHandler;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @Route("/communautes-thematiques", name="app_thematic_community_")
 */
class ThematicCommunityController extends Controller
{
    use CanaryControllerTrait;

    /** @var ThematicCommunityMembershipHandler */
    private $handler;

    public function __construct(ThematicCommunityMembershipHandler $handler)
    {
        $this->handler = $handler;
    }

    /**
     * @Route("", name="index", methods={"GET"})
     */
    public function indexAction(
        ThematicCommunityRepository $thematicCommunityRepository,
        ThematicCommunityMembershipRepository $membershipRepository,
        UserInterface $user = null
    ): Response {
        $this->disableInProduction();

        $joinedCommunities = [];
        if ($user) {
            $memberships = $membershipRepository->findAdherentMemberships($user);
            foreach ($memberships as $membership) {
                $joinedCommunities[(string) $membership->getCommunity()->getUuid()] = (string) $membership->getUuid();
            }
        }

        return $this->render('thematic_community/index.html.twig', [
            'thematic_communities' => $thematicCommunityRepository->findBy(['enabled' => true]),
            'joined_communities' => $joinedCommunities,
        ]);
    }

    /**
     * @Route("/{slug}", name="join", methods={"GET", "POST"})
     * @Entity("thematicCommunity", expr="repository.findOneBy({'slug': slug, 'enabled': true})")
     */
    public function joinAction(
        Request $request,
        ThematicCommunity $thematicCommunity,
        ThematicCommunityMembershipRepository $communityMembershipRepository,
        UserInterface $user = null,
        AnonymousFollowerSession $anonymousFollowerSession
    ): Response {
        $this->disableInProduction();

        if (
            $this->isGranted('IS_ANONYMOUS')
            && $authentication = $anonymousFollowerSession->start($request)
        ) {
            return $authentication;
        }

        if ($user) {
            if ($joinedMembership = $communityMembershipRepository->findOneBy(['adherent' => $user, 'community' => $thematicCommunity])) {
                return $this->redirectToRoute('app_thematic_community_membership_edit', ['id' => $joinedMembership->getId()]);
            }

            $membership = new AdherentMembership();
            $membership->setAdherent($user);
        } else {
            $membership = new ContactMembership();
            $membership->setContact(new Contact());
        }
        $membership->setCommunity($thematicCommunity);

        $form = $this->createForm(ThematicCommunityMembershipType::class, $membership);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $success = $this->handler->join($membership);

            if ($success) {
                $this->addFlash('info', \sprintf('Nous vous avons envoyé un email à l\'adresse "%s". Veuillez cliquer sur le lien contenu dans cet email pour confirmer votre inscription à la communauté.', $membership->getEmail()));
            } else {
                $this->addFlash('error', 'Cette adresse email est déjà enregistrée dans cette communauté.');
            }

            return $this->redirectToRoute('app_thematic_community_index');
        }

        return $this->render('thematic_community/join.html.twig', [
            'thematic_community' => $thematicCommunity,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/adhesion/{uuid}/modifier", name="membership_edit", methods={"GET", "POST"})
     * @Security("membership.getAdherent() == user")
     */
    public function editMembershipAction(Request $request, ThematicCommunityMembership $membership): Response
    {
        $this->disableInProduction();

        if ($membership->isPending()) {
            return $this->render('thematic_community/membership_pending.html.twig', [
                'membership' => $membership,
            ]);
        }

        $form = $this->createForm(ThematicCommunityMembershipType::class, $membership);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->handler->editMembership($membership);

            $this->addFlash('info', \sprintf('Vos préférences pour la communauté %s ont bien été sauvegardées.', $membership->getCommunity()->getName()));

            return $this->redirectToRoute('app_thematic_community_index');
        }

        return $this->render('thematic_community/join.html.twig', [
            'thematic_community' => $membership->getCommunity(),
            'membership' => $membership,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/adhesion/{uuid}/quitter", name="membership_leave", methods={"GET"})
     * @Security("membership.getAdherent() == user")
     */
    public function leaveMembershipAction(ThematicCommunityMembership $membership): RedirectResponse
    {
        $this->disableInProduction();

        $this->handler->unsubscribe($membership);

        $this->addFlash('info', \sprintf('Vous ne faites plus partie de la communauté %s.', $membership->getCommunity()->getName()));

        return $this->redirectToRoute('app_thematic_community_index');
    }

    /**
     * @Route("/adhesion/{uuid}/confirmation", name="membership_confirm", methods={"GET"})
     * @Security("membership.getAdherent() == user")
     */
    public function confirmMembershipAction(ThematicCommunityMembership $membership): RedirectResponse
    {
        $this->disableInProduction();

        $this->handler->confirmMembership($membership);

        $this->addFlash('info', sprintf('Vous êtes désormais membre de la communauté "%s"', $membership->getCommunity()->getName()));

        return $this->redirectToRoute('app_thematic_community_index');
    }

    /**
     * @Route("/adhesion/{uuid}/renvoyer-email", name="membership_resend_confirm_email", methods={"GET"})
     * @Security("membership.getAdherent() == user")
     */
    public function reSendConfirmationEmailAction(ThematicCommunityMembership $membership): RedirectResponse
    {
        $this->disableInProduction();

        $this->handler->sendConfirmEmail($membership);

        $this->addFlash('info', \sprintf('Un email de confirmation vous a été renvoyé à l\'adresse "%s".', $membership->getEmail()));

        return $this->redirectToRoute('app_thematic_community_index');
    }
}
