<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Donation;
use AppBundle\Form\DonationType;
use libphonenumber\PhoneNumber;
use Ramsey\Uuid\Uuid;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/don")
 */
class DonationController extends Controller
{
    /**
     * @Route("", name="donation_index", options={"expose"=true})
     * @Method({"GET", "POST"})
     */
    public function indexAction(Request $request)
    {
        $donation = $this->createDonationWithDefaultsFromRequest($request);

        $form = $this->createForm(DonationType::class, $donation, ['locale' => $request->getLocale()]);
        $form->add('submit', SubmitType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $donation->setId(Donation::creatUuid());
            $donation->setClientIp($request->getClientIp());

            $em = $this->getDoctrine()->getManager();
            $em->persist($donation);
            $em->flush();

            return $this->redirectToRoute('donation_pay', [
                'id' => $donation->getId()->toString(),
            ]);
        }

        return $this->render('donation/index.html.twig', [
            'form' => $form->createView(),
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

    private function createDonationWithDefaultsFromRequest(Request $request): Donation
    {
        $donation = new Donation();

        if ($amount = $request->query->getInt('montant')) {
            $donation->setAmount($amount);
        }

        if (($gender = $request->query->get('ge')) && in_array($gender, ['male', 'female'], true)) {
            $donation->setGender($gender);
        }

        if ($lastName = $request->query->get('ln')) {
            $donation->setLastName($lastName);
        }

        if ($firstName = $request->query->get('fn')) {
            $donation->setFirstName($firstName);
        }

        if ($email = $request->query->get('em')) {
            $donation->setEmail(urldecode($email));
        }

        if ($country = $request->query->get('co')) {
            $donation->setCountry($country);
        }

        if ($postalCode = $request->query->get('pc')) {
            $donation->setPostalCode($postalCode);
        }

        if ($city = $request->query->get('ci')) {
            $donation->setCity($city);
        }

        if ($address = $request->query->get('ad')) {
            $donation->setAddress(urldecode($address));
        }

        if (($phoneCode = $request->query->get('phc')) && ($phoneNumber = $request->query->get('phn'))) {
            $phone = new PhoneNumber();
            $phone->setCountryCode($phoneCode);
            $phone->setNationalNumber($phoneNumber);

            $donation->setPhone($phone);
        }

        return $donation;
    }
}
