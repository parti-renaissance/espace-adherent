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
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @Route("/je-donne")
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

        $form = $this->createForm(DonationType::class, $donation);
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
     * @Route("/paiement/{id}", name="donation_pay")
     * @Method("GET")
     */
    public function callAction(Donation $donation)
    {
        if ($donation->isFinished()) {
            throw $this->createNotFoundException();
        }

        $paybox = $this->get('lexik_paybox.request_handler');
        $paybox->setParameters([
            'PBX_CMD'          => 'CMD'.time(),
            'PBX_DEVISE'       => '978',
            'PBX_RETOUR'       => 'Mt:M;Ref:R;Auto:A;Erreur:E',
            'PBX_TYPEPAIEMENT' => 'CARTE',
            'PBX_TYPECARTE'    => 'CB',
            'PBX_RUF1'         => 'POST',
            'PBX_PORTEUR'      => $donation->getEmail(),
            'PBX_TOTAL'        => $donation->getAmount(),
            'PBX_EFFECTUE'     => $this->generateUrl(
                'donation_result',
                ['status' => 'success', 'id' => $donation->getId()->toString()],
                UrlGeneratorInterface::ABSOLUTE_URL
            ),
            'PBX_REFUSE'       => $this->generateUrl(
                'donation_result',
                ['status' => 'denied', 'id' => $donation->getId()->toString()],
                UrlGeneratorInterface::ABSOLUTE_URL
            ),
            'PBX_ANNULE'       => $this->generateUrl(
                'donation_result',
                ['status' => 'canceled', 'id' => $donation->getId()->toString()],
                UrlGeneratorInterface::ABSOLUTE_URL
            ),
            'PBX_REPONDRE_A'   => $this->generateUrl(
                'lexik_paybox_ipn',
                ['time' => time()],
                UrlGeneratorInterface::ABSOLUTE_URL
            ),
        ]);

        return $this->render('donation/call.html.twig', [
            'url'  => $paybox->getUrl(),
            'form' => $paybox->getForm()->createView(),
        ]);
    }

    /**
     * @Route("/resultat/{id}/{status}", name="donation_result")
     * @Method({"GET", "POST"})
     */
    public function returnAction(Donation $donation, $status)
    {
        dump($donation, $status);
        exit;
    }
}
