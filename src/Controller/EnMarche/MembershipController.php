<?php

namespace AppBundle\Controller\EnMarche;

use AppBundle\Address\GeoCoder;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\AdherentActivationToken;
use AppBundle\Exception\AdherentAlreadyEnabledException;
use AppBundle\Exception\AdherentTokenExpiredException;
use AppBundle\Form\AdherentRegistrationType;
use AppBundle\Form\BecomeAdherentType;
use AppBundle\Form\UserRegistrationType;
use AppBundle\Intl\UnitedNationsBundle;
use AppBundle\Membership\MembershipRequest;
use AppBundle\Membership\MembershipRequestHandler;
use AppBundle\OAuth\CallbackManager;
use GuzzleHttp\Exception\ConnectException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;

class MembershipController extends Controller
{
    /**
     * This action enables a guest user to adhere to the community.
     *
     * @Route("/inscription-utilisateur", name="app_membership_register")
     * @Method("GET|POST")
     */
    public function registerAction(Request $request, GeoCoder $geoCoder, AuthorizationChecker $authorizationChecker, CallbackManager $callbackManager): Response
    {
        if ($authorizationChecker->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $callbackManager->redirectToClientIfValid();
        }

        $membership = MembershipRequest::createWithCaptcha(
            $geoCoder->getCountryCodeFromIp($request->getClientIp()),
            $request->request->get('g-recaptcha-response')
        );

        $form = $this->createForm(UserRegistrationType::class, $membership);

        try {
            if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
                $this->get(MembershipRequestHandler::class)->registerAsUser($membership);

                return $this->redirectToRoute('app_membership_complete');
            }
        } catch (ConnectException $e) {
            $this->addFlash('error_recaptcha', $this->get('translator')->trans('recaptcha.error'));
        }

        return $this->render('membership/register.html.twig', [
            'membership' => $membership,
            'form' => $form->createView(),
            'countries' => UnitedNationsBundle::getCountries($request->getLocale()),
        ]);
    }

    /**
     * This action enables a guest user to adhere to the community.
     *
     * @Route("/adhesion", name="app_membership_join")
     * @Method("GET|POST")
     */
    public function adhesionAction(Request $request, GeoCoder $geoCoder): Response
    {
        if ($this->isGranted('ROLE_USER')) {
            return $this->joinAdherent($request);
        }

        $membership = MembershipRequest::createWithCaptcha(
            $geoCoder->getCountryCodeFromIp($request->getClientIp()),
            $request->request->get('g-recaptcha-response')
        );

        $form = $this
            ->createForm(AdherentRegistrationType::class, $membership)
            ->add('submit', SubmitType::class, ['label' => 'Je rejoins La République En Marche'])
            ->handleRequest($request)
        ;

        try {
            if ($form->isSubmitted() && $form->isValid()) {
                $this->get(MembershipRequestHandler::class)->registerAsAdherent($membership);

                return $this->redirectToRoute('app_membership_complete');
            }
        } catch (ConnectException $e) {
            $this->addFlash('error_recaptcha', $this->get('translator')->trans('recaptcha.error'));
        }

        return $this->render('membership/join.html.twig', [
            'membership' => $membership,
            'form' => $form->createView(),
            'countries' => UnitedNationsBundle::getCountries($request->getLocale()),
        ]);
    }

    private function joinAdherent(Request $request): Response
    {
        /** @var Adherent $user */
        $user = $this->getUser();

        if ($user->isAdherent()) {
            throw $this->createNotFoundException();
        }

        $membership = MembershipRequest::createFromAdherent($user);
        $form = $this->createForm(BecomeAdherentType::class, $membership)
            ->add('submit', SubmitType::class, ['label' => 'Je rejoins La République En Marche'])
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            $this->get(MembershipRequestHandler::class)->join($user, $membership);

            $this->getDoctrine()->getManager()->flush();

            $this->get('security.token_storage')->setToken(null);
            $request->getSession()->invalidate();
            $this->get('app.security.authentication_utils')->authenticateAdherent($user);

            $this->addFlash('info', $this->get('translator')->trans('adherent.activation.success'));

            return $this->redirectToRoute('app_adherent_home');
        }

        return $this->render('membership/join.html.twig', [
            'membership' => $membership,
            'form' => $form->createView(),
            'countries' => UnitedNationsBundle::getCountries($request->getLocale()),
        ]);
    }

    /**
     * This action is the landing page at the end of the subscription process.
     *
     * @Route("/presque-fini", name="app_membership_complete")
     * @Method("GET")
     */
    public function completeAction(): Response
    {
        if ($this->getUser()) {
            $this->redirectToRoute('homepage');
        }

        return $this->render('membership/complete.html.twig');
    }

    /**
     * This action enables a new user to activate his\her newly created
     * membership account.
     *
     * @Route(
     *   path="/inscription/finaliser/{adherent_uuid}/{activation_token}",
     *   name="app_membership_activate",
     *   requirements={
     *     "adherent_uuid": "%pattern_uuid%",
     *     "activation_token": "%pattern_sha1%"
     *   }
     * )
     * @Method("GET")
     * @Entity("adherent", expr="repository.findOneByUuid(adherent_uuid)")
     * @Entity("activationToken", expr="repository.findByToken(activation_token)")
     */
    public function activateAction(Adherent $adherent, AdherentActivationToken $activationToken, CallbackManager $callbackManager): Response
    {
        if ($this->getUser()) {
            $this->redirectToRoute('app_search_events');
        }

        try {
            $this->get('app.adherent_account_activation_handler')->handle($adherent, $activationToken);

            if ($adherent->isAdherent()) {
                $this->get(MembershipRequestHandler::class)->sendConfirmationJoinMessage($adherent);

                return $callbackManager->redirectToClientIfValid('app_adherent_home', ['from_activation' => 1]);
            }

            return $callbackManager->redirectToClientIfValid('app_membership_join');
        } catch (AdherentAlreadyEnabledException $e) {
            $this->addFlash('info', $this->get('translator')->trans('adherent.activation.already_active'));
        } catch (AdherentTokenExpiredException $e) {
            $this->addFlash('info', $this->get('translator')->trans('adherent.activation.expired_key'));
        }

        // Other exceptions that may be raised will be caught by Symfony.

        return $this->redirectToRoute('app_user_login');
    }
}
