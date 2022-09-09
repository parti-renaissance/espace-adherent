<?php

namespace App\Controller\Renaissance\Donation;

use App\Form\Renaissance\Donation\DonationRequestDonatorType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(path="/don/coordonnees", name="app_renaissance_donation_informations", methods={"GET|POST"})
 */
class DonatorInfoController extends AbstractDonationController
{
    public function __invoke(Request $request): Response
    {
        $command = $this->getCommand();

        if (!$this->processor->canFillPersonalInfo($command)) {
            return $this->redirectToRoute('app_renaissance_donation');
        }

        $this->processor->doFillPersonalInfo($command);

        $form = $this
            ->createForm(DonationRequestDonatorType::class, $command)
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            $this->processor->doAcceptTermsAndConditions($command);

            return $this->redirectToRoute('app_renaissance_donation_mentions');
        }

        return $this->render('renaissance/donation/fill_personal_info.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
