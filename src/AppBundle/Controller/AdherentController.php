<?php

namespace AppBundle\Controller;

use AppBundle\Committee\CommitteeCreationCommand;
use AppBundle\Form\CreateCommitteeCommandType;
use AppBundle\Intl\UnitedNationsBundle;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/espace-adherent")
 */
class AdherentController extends Controller
{
    /**
     * @Route("/mon-profil", name="app_adherent_profile")
     */
    public function profileAction(): Response
    {
        return $this->render('adherent/profile.html.twig');
    }

    /**
     * This action enables a new user to pin his/her interests.
     *
     * @Route("/centres-interets", name="app_adherent_pin_interests")
     */
    public function pinInterestsAction(Request $request): Response
    {
        // User may not be activated, if so its ID is in the session
        // see registerAction above.
        return new Response('TO BE IMPLEMENTED');
    }

    /**
     * This action enables an adherent to create a committee.
     *
     * @Route("/creer-mon-comite", name="app_adherent_create_committee")
     * @Method("GET|POST")
     * @Security("is_granted('CREATE_COMMITTEE')")
     */
    public function createCommitteeAction(Request $request): Response
    {
        $command = new CommitteeCreationCommand($user = $this->getUser());
        $form = $this->createForm(CreateCommitteeCommandType::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->get('app.committee.committee_creation_handler')->handle($command);
            $this->addFlash('info', $this->get('translator')->trans('committee.creation.success'));

            return $this->redirectToRoute('app_committee_show', [
                'uuid' => $command->getCommitteeUuid(),
                'slug' => $command->getCommitteeSlug(),
            ]);
        }

        return $this->render('adherent/create_committee.html.twig', [
            'form' => $form->createView(),
            'adherent' => $user,
            'committee' => $command,
            'countries' => UnitedNationsBundle::getCountries($request->getLocale()),
        ]);
    }
}
