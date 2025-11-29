<?php

declare(strict_types=1);

namespace App\Controller\BesoinDEurope;

use App\BesoinDEurope\Donation\DonationRequest;
use App\Donation\Systempay\RequestParamsBuilder;
use App\Form\BesoinDEurope\DonationRequestType;
use App\Security\Http\Session\AnonymousFollowerSession;
use App\Utils\UtmParams;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

class DonationController extends AbstractController
{
    public function __construct(
        private readonly CsrfTokenManagerInterface $csrfTokenManager,
        private readonly RequestParamsBuilder $requestParamsBuilder,
        private readonly AnonymousFollowerSession $anonymousFollowerSession,
        private readonly string $friendlyCaptchaEuropeSiteKey,
    ) {
    }

    #[Route(path: '/don', name: 'app_bde_donation', methods: ['GET', 'POST'])]
    public function formAction(Request $request): Response
    {
        if ($response = $this->anonymousFollowerSession->start($request)) {
            return $response;
        }

        $donationRequest = $this->getDonationRequest($request);

        $form = $this
            ->createForm(DonationRequestType::class, $donationRequest)
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            return $this->render('besoindeurope/donation/payment.html.twig', [
                'params' => $this->requestParamsBuilder->build($donationRequest),
            ]);
        }

        return $this->renderForm('besoindeurope/donation/form.html.twig', [
            'form' => $form,
            'email_validation_token' => $this->csrfTokenManager->getToken('email_validation_token'),
            'step' => $donationRequest->hasAmount() ? 1 : 0,
        ]);
    }

    #[Route(path: '/don/merci', name: 'app_bde_donation_thanks', methods: 'GET')]
    public function thanksAction(): Response
    {
        return $this->render('besoindeurope/donation/thanks.html.twig');
    }

    private function getDonationRequest(Request $request): DonationRequest
    {
        $donationRequest = new DonationRequest();

        if ($user = $this->getUser()) {
            $donationRequest->updateFromAdherent($user);
        } else {
            $donationRequest->email = $request->query->get('email');
        }

        $donationRequest->amount = $request->query->getInt('amount');

        if ($request->query->has(UtmParams::UTM_SOURCE)) {
            $donationRequest->utmSource = UtmParams::filterUtmParameter($request->query->get(UtmParams::UTM_SOURCE));
        }
        if ($request->query->has(UtmParams::UTM_CAMPAIGN)) {
            $donationRequest->utmCampaign = UtmParams::filterUtmParameter($request->query->get(UtmParams::UTM_CAMPAIGN));
        }

        $donationRequest->setRecaptchaSiteKey($this->friendlyCaptchaEuropeSiteKey);
        $donationRequest->setRecaptcha($request->request->get('frc-captcha-solution'));

        return $donationRequest;
    }
}
