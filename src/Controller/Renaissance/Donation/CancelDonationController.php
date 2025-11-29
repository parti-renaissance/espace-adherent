<?php

declare(strict_types=1);

namespace App\Controller\Renaissance\Donation;

use App\Donation\Paybox\PayboxPaymentUnsubscription;
use App\Entity\Adherent;
use App\Exception\PayboxPaymentUnsubscriptionException;
use App\Form\ConfirmActionType;
use App\Repository\DonationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class CancelDonationController extends AbstractController
{
    #[Route(path: '/don/mensuel/annuler', name: 'app_renaissance_donation__cancel_subscription', methods: ['GET', 'POST'])]
    public function __invoke(
        EntityManagerInterface $manager,
        Request $request,
        DonationRepository $donationRepository,
        PayboxPaymentUnsubscription $payboxPaymentUnsubscription,
        LoggerInterface $logger,
    ): Response {
        /** @var Adherent $adherent */
        $adherent = $this->getUser();

        $donations = $donationRepository->findAllSubscribedDonationByEmail($adherent->getEmailAddress());

        if (!$donations) {
            $this->addFlash('error', 'Aucun don mensuel n\'a été trouvé');

            return $this->redirect($this->generateUrl('app_my_donations_show_list', [], UrlGeneratorInterface::ABSOLUTE_URL));
        }

        $form = $this->createForm(ConfirmActionType::class)->handleRequest($request);
        if ($form->isSubmitted()) {
            if ($form->get('allow')->isClicked()) {
                foreach ($donations as $donation) {
                    try {
                        $payboxPaymentUnsubscription->unsubscribe($donation);
                        $manager->flush();
                        $payboxPaymentUnsubscription->sendConfirmationMessage($donation, $adherent);
                        $this->addFlash(
                            'success',
                            'Votre don mensuel a bien été annulé. Vous recevrez bientôt un email de confirmation.'
                        );
                        $logger->info(\sprintf('Subscription donation id(%d) from user email %s have been cancel successfully.', $donation->getId(), $adherent->getEmailAddress()));
                    } catch (PayboxPaymentUnsubscriptionException $e) {
                        $this->addFlash('error', 'La requête n\'a pas abouti, veuillez réessayer s\'il vous plait. Si le problème persiste, merci de nous envoyer un email à dons@parti-renaissance.fr');

                        $logger->error(\sprintf('Subscription donation id(%d) from user email %s have an error.', $donation->getId(), $adherent->getEmailAddress()), ['exception' => $e]);
                    }
                }
            }

            return $this->redirect($this->generateUrl('app_my_donations_show_list', [], UrlGeneratorInterface::ABSOLUTE_URL));
        }

        return $this->render('renaissance/adherent/my_donations/donation_subscription_cancel_confirmation.html.twig', [
            'confirmation_form' => $form->createView(),
        ]);
    }
}
