<?php

namespace AppBundle\Controller;

use AppBundle\Entity\ActivationKey;
use AppBundle\Entity\Adherent;
use AppBundle\Exception\ActivationKeyExpiredException;
use AppBundle\Exception\AdherentAlreadyEnabledException;
use AppBundle\Intl\UnitedNationsBundle;
use AppBundle\Membership\MembershipRequest;
use AppBundle\Form\MembershipRequestType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class MembershipController extends Controller
{
    /**
     * This action enables a guest user to adhere to the community.
     *
     * @Route("/inscription", name="app_membership_register")
     * @Method("GET|POST")
     */
    public function registerAction(Request $request): Response
    {
        $membership = MembershipRequest::createWithCaptcha($request->request->get('g-recaptcha-response'));
        $form = $this->createForm(MembershipRequestType::class, $membership);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->get('app.membership_request_handler')->handle($membership);
            $this->addFlash('info', $this->get('translator')->trans('adherent.registration.success'));

            return $this->redirectToRoute('app_membership_register');
        }

        return $this->render('membership/registration.html.twig', [
            'membership' => $membership,
            'form' => $form->createView(),
            'countries' => UnitedNationsBundle::getCountries($request->getLocale()),
        ]);
    }

    /**
     * This action enables a guest user to activate his\her newly created
     * membership account.
     *
     * @Route(
     *   path="/inscription/finaliser/{adherent_uuid}/{activation_key}",
     *   name="app_membership_activate",
     *   requirements={
     *     "adherent_uuid": "%pattern_uuid%",
     *     "activation_key": "%pattern_sha1%"
     *   }
     * )
     * @Method("GET")
     * @Entity("adherent", expr="repository.findOneByUuid(adherent_uuid)")
     * @Entity("activationKey", expr="repository.findByToken(activation_key)")
     */
    public function activateAction(Adherent $adherent, ActivationKey $activationKey): Response
    {
        $manager = $this->getDoctrine()->getManager();

        try {
            $adherent->activate($activationKey);
            //$manager->persist($adherent);
            //$manager->persist($activationKey);
            $this->addFlash('info', $this->get('translator')->trans('adherent.activation.success'));
        } catch (AdherentAlreadyEnabledException $e) {
            $this->addFlash('info', $this->get('translator')->trans('adherent.activation.already_active'));
        } catch (ActivationKeyExpiredException $e) {
            $this->addFlash('info', $this->get('translator')->trans('adherent.activation.expired_key'));
        }

        // Other exceptions that may be raised will be caught by Symfony.

        $manager->flush();

        return $this->redirectToRoute('adherent_login');
    }
}
