<?php

namespace App\Controller\EnMarche\TerritorialCouncil;

use App\Form\TerritorialCouncil\ConvocationType;
use App\Repository\TerritorialCouncil\ConvocationRepository;
use App\TerritorialCouncil\Convocation\ConvocationObject;
use App\TerritorialCouncil\Convocation\Manager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @Route(path="/espace-referent/instances/convocations", name="app_instances_convocation_referent")
 *
 * @Security("is_granted('ROLE_REFERENT')")
 */
class ConvocationManagerController extends AbstractController
{
    /**
     * @Route("", name="_list", methods={"GET"})
     */
    public function listAction(UserInterface $adherent, Request $request, ConvocationRepository $repository): Response
    {
        return $this->render(
            'referent/territorial_council/convocation_list.html.twig',
            ['paginator' => $repository->getPaginator($adherent, $request->query->getInt('page', 1))]
        );
    }

    /**
     * @Route("/creer", name="_create", methods={"GET", "POST"})
     */
    public function createAction(UserInterface $adherent, Request $request, Manager $manager): Response
    {
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
