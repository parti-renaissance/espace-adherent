<?php

namespace AppBundle\Controller;

use AppBundle\Intl\UnitedNationsBundle;
use AppBundle\Membership\MembershipRequest;
use AppBundle\Form\MembershipRequestType;
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
            $adherent = $this->get('app.membership.adherent_factory')->createFromMembershipRequest($membership);
            $em = $this->getDoctrine()->getManager();
            $em->persist($adherent);
            $em->flush();

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
     * @Route("/inscription/finaliser", name="app_membership_activate")
     * @Method("GET")
     */
    public function activateAction(Request $request): Response
    {
        return new Response('TO BE IMPLEMENTED');
    }
}
