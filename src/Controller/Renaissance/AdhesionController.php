<?php

namespace App\Controller\Renaissance;

use App\Adhesion\MembershipRequest;
use App\Controller\CanaryControllerTrait;
use App\Form\MembershipRequestType;
use App\Membership\MembershipRequest\RenaissanceMembershipRequest;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

#[Route('/v2/adhesion', name: 'app_adhesion_index', methods: ['GET', 'POST'])]
class AdhesionController extends AbstractController
{
    use CanaryControllerTrait;

    public function __construct(private readonly CsrfTokenManagerInterface $csrfTokenManager)
    {
    }

    public function __invoke(Request $request): Response
    {
        $this->disableInProduction();

        $membershipRequest = new MembershipRequest();
        $membershipRequest->email = $request->query->get('email');

        if ($request->query->has(RenaissanceMembershipRequest::UTM_SOURCE)) {
            $membershipRequest->utmSource = $this->filterUtmParameter((string) $request->query->get(RenaissanceMembershipRequest::UTM_SOURCE));
            $membershipRequest->utmCampaign = $this->filterUtmParameter((string) $request->query->get(RenaissanceMembershipRequest::UTM_CAMPAIGN));
        }

        $form = $this
            ->createForm(MembershipRequestType::class, $membershipRequest)
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            return $this->redirect('app_adhesion_index');
        }

        return $this->renderForm('renaissance/adhesion/form.html.twig', [
            'form' => $form,
            'email_validation_token' => $this->csrfTokenManager->getToken('email_validation_token'),
        ]);
    }

    private function filterUtmParameter($utmParameter): ?string
    {
        if (!$utmParameter) {
            return null;
        }

        return mb_substr($utmParameter, 0, 255);
    }
}
