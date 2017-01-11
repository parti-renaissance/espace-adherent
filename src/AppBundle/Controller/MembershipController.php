<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Donation;
use AppBundle\Form\DonationType;
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
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
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

            if ($membership->hasAdherent()) {
                $this->get('app.membership_utils')->createRegisteringDonation($membership->getAdherent());
            }

            return $this->redirectToRoute('app_membership_donate');
        }

        return $this->render('membership/register.html.twig', [
            'membership' => $membership,
            'form' => $form->createView(),
            'countries' => UnitedNationsBundle::getCountries($request->getLocale()),
        ]);
    }

    /**
     * This action enables a new user to donate as a second step of the
     * registration process, thus he/she may not be logged-in/activated yet.
     *
     * @Route("/inscription/don", name="app_membership_donate")
     * @Method("GET|POST")
     */
    public function donateAction(Request $request): Response
    {
        $memberShipUtils = $this->get('app.membership_utils');

        if (!$donation = $memberShipUtils->getRegisteringDonation()) {
            throw $this->createNotFoundException('The adherent has not been successfully redirected from the registration page.');
        }

        $form = $this->createForm(DonationType::class, $donation, [
            'locale' => $request->getLocale(),
            'submit_label' => 'adherent.submit_donation_label',
            'sponsor_form' => false,
        ])
            // TODO add this field only if anonymous (adherent not activated yet)
            ->add('pass', SubmitType::class)
        ;

        if ($form->handleRequest($request)->isSubmitted()) {
            if ($form->has('pass') && $form->get('pass')->isClicked()) {
                return $this->redirectToRoute('app_adherent_pin_interests');
            }

            if ($form->isValid()) {
                $memberShipUtils->clearRegisteringDonation();
                $this->get('app.donation.manager')->persist($donation, $request->getClientIp());

                return $this->redirectToRoute('donation_pay', [
                    'id' => $donation->getId()->toString(),
                ]);
            }
        }

        return $this->render('membership/donate.html.twig', [
            'form' => $form->createView(),
            'donation' => $donation,
            'countries' => UnitedNationsBundle::getCountries($request->getLocale()),
        ]);
    }

    /**
     * This action enables a new user to activate his\her newly created
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
