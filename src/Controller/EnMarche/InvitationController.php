<?php

namespace AppBundle\Controller\EnMarche;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\Invite;
use AppBundle\Form\InvitationType;
use AppBundle\Invitation\InvitationRequestHandler;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @Route("/invitation", name="invitation_form", methods={"GET", "POST"})
 * @Route("/adherent-invitation", name="app_adherent_invitation", methods={"GET", "POST"}, defaults={"fromConnectedAdherent": true})
 */
class InvitationController extends Controller
{
    /**
     * @var Adherent|null
     */
    public function __invoke(
        Request $request,
        InvitationRequestHandler $handler,
        ?bool $fromConnectedAdherent,
        ?UserInterface $adherent
    ) {
        $invite = Invite::createWithCaptcha((string) $request->request->get('g-recaptcha-response'));

        $fromConnectedAdherent = $fromConnectedAdherent && $adherent instanceof Adherent;

        if ($fromConnectedAdherent) {
            $invite->setFirstName($adherent->getFirstName());
            $invite->setLastName($adherent->getLastName());
        }

        $form = $this
            ->createForm(InvitationType::class, $invite, ['from_adherent' => $fromConnectedAdherent])
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
}
