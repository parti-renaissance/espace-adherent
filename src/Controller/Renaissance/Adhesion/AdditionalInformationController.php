<?php

namespace App\Controller\Renaissance\Adhesion;

use App\Entity\Adherent;
use App\Form\Renaissance\Adhesion\AdditionalInfoType;
use App\Membership\MembershipRegistrationProcess;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/v1/adhesion/informations-additionelles', name: 'app_renaissance_adhesion_additional_informations', methods: ['GET|POST'])]
#[IsGranted('ROLE_ADHERENT')]
class AdditionalInformationController extends AbstractController
{
    public function __invoke(
        Request $request,
        EntityManagerInterface $entityManager,
        MembershipRegistrationProcess $membershipRegistrationProcess
    ): Response {
        /** @var Adherent $adherent */
        $adherent = $this->getUser();

        $form = $this
            ->createForm(AdditionalInfoType::class, $adherent, ['from_certified_adherent' => $adherent->isCertified()])
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_renaissance_adhesion_finish');
        }

        return $this->render('renaissance/adhesion/additional_information.html.twig', [
           'form' => $form->createView(),
        ]);
    }
}
