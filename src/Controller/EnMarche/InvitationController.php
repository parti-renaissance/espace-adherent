<?php

namespace AppBundle\Controller\EnMarche;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\Invite;
use AppBundle\Form\InvitationType;
use AppBundle\Form\SimpleInvitationType;
use AppBundle\Invitation\InvitationRequestHandler;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

class InvitationController extends Controller
{
    /**
     * @Route("/invitation", name="invitation_form", methods={"GET", "POST"})
     */
    public function inviteAction(Request $request, InvitationRequestHandler $handler): Response
    {
        $invite = Invite::createWithCaptcha((string) $request->request->get('g-recaptcha-response'));

        $form = $this
            ->createForm(InvitationType::class, $invite)
            ->add('submit', SubmitType::class)
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            $handler->handle($invite, $request);

            return $this->render('invitation/sent.html.twig', [
                'invite' => $invite,
            ]);
        }

        return $this->render('invitation/form.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @var Adherent
     *
     * @Route("/espace-referent/invitation", name="app_referent_adherent_invitation", methods={"GET", "POST"}, defaults={"type": "referent"})
     * @Route("/espace-comite/invitation", name="app_supervisor_adherent_invitation", methods={"GET", "POST"}, defaults={"type": "supervisor"})
     * @Route("/espace-depute/invitation", name="app_deputy_adherent_invitation", methods={"GET", "POST"}, defaults={"type": "deputy"})
     * @Route("/espace-senateur/invitation", name="app_senator_adherent_invitation", methods={"GET", "POST"}, defaults={"type": "senator"})
     *
     * @Security("is_granted('ROLE_ADHERENT')")
     */
    public function connectedAdherentInviteAction(
        Request $request,
        UserInterface $adherent,
        InvitationRequestHandler $handler,
        string $type
    ): Response {
        $invite = new Invite();
        $invite->setFirstName($adherent->getFirstName());
        $invite->setLastName($adherent->getLastName());

        $form = $this
            ->createForm(SimpleInvitationType::class)
            ->add('submit', SubmitType::class)
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $invite->setEmail($data['email']);
            $invite->setMessage($data['message']);

            $handler->handle($invite, $request);

            return $this->render('invitation/sent.html.twig', [
                'invite' => $invite,
                'type' => $type,
            ]);
        }

        return $this->render('invitation/simple_form.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
