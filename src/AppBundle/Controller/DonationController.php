<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Donation;
use AppBundle\Form\DonationType;
use AppBundle\Intl\UnitedNationsBundle;
use Ramsey\Uuid\Uuid;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/don")
 */
class DonationController extends Controller
{
    /**
     * @Route(name="donation_index", options={"expose"=true})
     * @Method({"GET", "POST"})
     */
    public function indexAction(Request $request)
    {
        $donation = $this->get('app.donation.factory')->createDonationFromRequest($request);
        $form = $this->createForm(DonationType::class, $donation, ['locale' => $request->getLocale()]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->get('app.donation.manager')->persist($donation, $request->getClientIp());

            return $this->redirectToRoute('donation_pay', [
                'id' => $donation->getId()->toString(),
            ]);
        }

        return $this->render('donation/index.html.twig', [
            'form' => $form->createView(),
            'donation' => $donation,
            'countries' => UnitedNationsBundle::getCountries($request->getLocale()),
        ]);
    }

    /**
     * @Route("/{id}/paiement", name="donation_pay", requirements={"id"="^[0-9A-Fa-f]{8}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{12}$"})
     * @Method("GET")
     */
    public function payboxAction(Donation $donation)
    {
        if ($donation->isFinished()) {
            return $this->redirectToRoute('donation_index');
        }

        $paybox = $this->get('app.donation.form_factory')->createPayboxFormForDonation($donation);

        return $this->render('donation/paybox.html.twig', [
            'url' => $paybox->getUrl(),
            'form' => $paybox->getForm()->createView(),
        ]);
    }

    /**
     * @Route("/callback", name="donation_callback")
     * @Method("GET")
     */
    public function callbackAction(Request $request)
    {
        $id = $request->query->get('id');

        if (!$id || !Uuid::isValid($id)) {
            return $this->redirectToRoute('donation_index');
        }

        return $this->get('app.donation.transaction_callback_handler')->handle($id, $request);
    }

    /**
     * @Route("/{id}/{status}", name="donation_result", requirements={"status"="effectue|erreur", "id"="^[0-9A-Fa-f]{8}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{12}$"})
     * @Method("GET")
     */
    public function resultAction(Request $request, Donation $donation)
    {
        $retryUrl = $this->generateUrl('donation_index', [
            'montant' => $donation->getAmount(),
            'ge' => $donation->getGender(),
            'ln' => $donation->getLastName(),
            'fn' => $donation->getFirstName(),
            'em' => urlencode($donation->getEmail()),
            'co' => $donation->getCountry(),
            'pc' => $donation->getPostalCode(),
            'ci' => $donation->getCity(),
            'ad' => urlencode($donation->getAddress()),
            'phc' => $donation->getPhone()->getCountryCode(),
            'phn' => $donation->getPhone()->getNationalNumber(),
        ]);

        return $this->render('donation/result.html.twig', [
            'successful' => $donation->isSuccessful(),
            'error_code' => $request->query->get('code'),
            'donation' => $donation,
            'retry_url' => $retryUrl,
        ]);
    }
}
