<?php

namespace App\Controller\EnMarche;

use App\Entity\Adherent;
use App\Entity\Committee;
use App\Entity\Invite;
use App\Form\InvitationType;
use App\Form\SimpleInvitationType;
use App\Invitation\InvitationRequestHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class InvitationController extends AbstractController
{
    use AccessDelegatorTrait;

    #[Route(path: '/invitation', name: 'invitation_form', methods: ['GET', 'POST'])]
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

    #[IsGranted('ROLE_USER')]
    #[Route(path: '/espace-depute/invitation', name: 'app_deputy_adherent_invitation', methods: ['GET', 'POST'], defaults: ['type' => 'deputy'])]
    #[Route(path: '/espace-comite/{slug}/invitation', name: 'app_supervisor_adherent_invitation', methods: ['GET', 'POST'], defaults: ['type' => 'supervisor'])]
    #[Route(path: '/espace-candidat-legislative/invitation', name: 'app_legislative_candidate_adherent_invitation', methods: ['GET', 'POST'], defaults: ['type' => 'legislative_candidate'])]
    #[Route(path: '/espace-candidat/invitation', name: 'app_candidate_adherent_invitation', methods: ['GET', 'POST'], defaults: ['type' => 'candidate'])]
    public function connectedAdherentInviteAction(
        Request $request,
        InvitationRequestHandler $handler,
        string $type,
        ?Committee $committee = null,
    ): Response {
        $adherent = $this->getMainUser($request->getSession());

        /** @var Adherent $adherent */
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

            return $this->render('invitation/adherent_space/sent.html.twig', [
                'invite' => $invite,
                'committee' => $committee,
                'type' => $type,
                'base_template' => $this->getBaseTemplatePath($type),
            ]);
        }

        return $this->render('invitation/adherent_space/invitation_form.html.twig', [
            'form' => $form->createView(),
            'committee' => $committee,
            'type' => $type,
            'base_template' => $this->getBaseTemplatePath($type),
        ]);
    }

    private function getBaseTemplatePath(string $type): string
    {
        return 'invitation/adherent_space/_base_'.$type.'_space.html.twig';
    }
}
