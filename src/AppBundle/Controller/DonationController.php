<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Donation;
use AppBundle\Form\DonationType;
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
     * @Route("", name="donation_index")
     * @Method({"GET", "POST"})
     */
    public function indexAction(Request $request)
    {
        $donation = new Donation();

        $form = $this->createForm(DonationType::class, $donation, ['locale' => $request->getLocale()]);
        $form->add('submit', SubmitType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $donation->setId(Uuid::uuid4());

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
    public function payAction(Donation $donation)
    {
        if ($donation->isFinished()) {
            return $this->redirectToRoute('donation_index');
        }

        $paybox = $this->get('app.donation.form_factory')->createPayboxFormForDonation($donation);

        return $this->render('donation/call.html.twig', [
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

        $donation = $this->getDoctrine()->getManager()->find('AppBundle:Donation', $id);

        return $this->get('app.donation.result_code_handler')->createRedirectResponseForDonation($donation);
    }

    /**
     * @Route("/{id}/{status}", name="donation_result", requirements={"id"="^[0-9A-Fa-f]{8}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{12}$"})
     * @Method("GET")
     */
    public function resultAction(Donation $donation, $status)
    {
        // TODO

        dump($status, $donation);
        exit;
    }
}
