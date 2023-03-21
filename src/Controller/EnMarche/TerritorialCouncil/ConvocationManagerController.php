<?php

namespace App\Controller\EnMarche\TerritorialCouncil;

use App\Entity\Adherent;
use App\Form\TerritorialCouncil\ConvocationType;
use App\Repository\TerritorialCouncil\ConvocationRepository;
use App\TerritorialCouncil\Convocation\ConvocationObject;
use App\TerritorialCouncil\Convocation\Manager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/espace-referent/instances/convocations', name: 'app_instances_convocation_referent')]
#[IsGranted('ROLE_REFERENT')]
class ConvocationManagerController extends AbstractController
{
    #[Route(path: '', name: '_list', methods: ['GET'])]
    public function listAction(Request $request, ConvocationRepository $repository): Response
    {
        /** @var Adherent $adherent */
        $adherent = $this->getUser();

        return $this->render(
            'referent/territorial_council/convocation_list.html.twig',
            ['paginator' => $repository->getPaginator($adherent, $request->query->getInt('page', 1))]
        );
    }

    #[Route(path: '/creer', name: '_create', methods: ['GET', 'POST'])]
    public function createAction(Request $request, Manager $manager): Response
    {
        /** @var Adherent $adherent */
        $adherent = $this->getUser();
        $form = $this
            ->createForm(ConvocationType::class, $object = new ConvocationObject())
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            $manager->create($object, $adherent);

            $this->addFlash('info', 'La convocation a bien été créée.');

            return $this->redirectToRoute('app_instances_convocation_referent_list');
        }

        return $this->render(
            'referent/territorial_council/convocation_create.html.twig',
            ['form' => $form->createView()]
        );
    }
}
