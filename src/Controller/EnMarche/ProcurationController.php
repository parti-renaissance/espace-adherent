<?php

namespace AppBundle\Controller\EnMarche;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\ProcurationProxy;
use AppBundle\Exception\InvalidUuidException;
use AppBundle\Form\ProcurationProfileType;
use AppBundle\Form\ProcurationElectionsType;
use AppBundle\Form\ProcurationProxyType;
use AppBundle\Form\ProcurationVoteType;
use AppBundle\Entity\ProcurationRequest;
use AppBundle\Procuration\ProcurationRequestSession;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/procuration")
 */
class ProcurationController extends Controller
{
    /**
     * @Route(defaults={"_enable_campaign_silence"=true}, name="app_procuration_landing")
     * @Method("GET")
     */
    public function landingAction(): Response
    {
        return $this->render('procuration/landing.html.twig');
    }

    /**
     * @Route("/je-demande", defaults={"_enable_campaign_silence"=true}, name="app_procuration_index")
     * @Method("GET")
     */
    public function indexAction(Request $request, ProcurationRequestSession $procurationSession): Response
    {
        $procurationSession->start();

        return $this->render('procuration/index.html.twig', [
            'has_error' => $request->query->getBoolean('has_error'),
            'form' => $this->createForm(ProcurationVoteType::class, $procurationSession->getCurrentModel())->createView(),
        ]);
    }

    /**
     * @Route("/je-demande/mon-lieu-de-vote", defaults={"_enable_campaign_silence"=true}, name="app_procuration_request_vote")
     * @Method("GET|POST")
     */
    public function voteAction(Request $request, ProcurationRequestSession $procurationSession): Response
    {
        $form = $this->createForm(ProcurationVoteType::class, $procurationSession->getCurrentModel())
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            return $this->redirectToRoute('app_procuration_request_profile');
        }

        return $this->render('procuration/vote.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/je-demande/mes-coordonnees", defaults={"_enable_campaign_silence"=true}, name="app_procuration_request_profile")
     * @Method("GET|POST")
     */
    public function profileAction(Request $request, ProcurationRequestSession $procurationSession): Response
    {
        $procurationRequest = $procurationSession->getCurrentModel();

        if ($this->getUser() instanceof Adherent) {
            $procurationRequest->importAdherentData($this->getUser());
        }

        $form = $this->createForm(ProcurationProfileType::class, $procurationRequest)
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            return $this->redirectToRoute('app_procuration_request_elections');
        }

        return $this->render('procuration/profile.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/je-demande/ma-procuration", defaults={"_enable_campaign_silence"=true}, name="app_procuration_request_elections")
     * @Method("GET|POST")
     */
    public function electionsAction(Request $request, ProcurationRequestSession $procurationSession): Response
    {
        $procurationRequest = $procurationSession->getCurrentModel();
        $procurationRequest->recaptcha = (string) $request->request->get('g-recaptcha-response');

        $form = $this->createForm(ProcurationElectionsType::class, $procurationRequest)
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            $manager = $this->getDoctrine()->getManager();

            $manager->persist($procurationRequest);
            $manager->flush();
            $procurationSession->end();

            return $this->redirectToRoute('app_procuration_request_thanks');
        }

        return $this->render('procuration/elections.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/je-demande/merci", defaults={"_enable_campaign_silence"=true}, name="app_procuration_request_thanks")
     * @Method("GET")
     */
    public function requestThanksAction(): Response
    {
        return $this->render('procuration/thanks.html.twig');
    }

    /**
     * @Route("/je-propose", defaults={"_enable_campaign_silence"=true}, name="app_procuration_proxy_proposal")
     * @Method("GET|POST")
     */
    public function proxyProposalAction(Request $request): Response
    {
        $referent = null;

        if ($referentUuid = $request->query->get('uuid')) {
            try {
                $referent = $this->getDoctrine()->getRepository(Adherent::class)->findOneByValidUuid($referentUuid);
            } catch (InvalidUuidException $e) {
            } finally {
                if (isset($e) || !$referent instanceof Adherent || !$referent->canBeProxy()) {
                    return $this->redirectToRoute('app_procuration_proxy_proposal');
                }
            }
        }

        $proposal = new ProcurationProxy($referent);
        $proposal->recaptcha = $request->request->get('g-recaptcha-response');

        $user = $this->getUser();

        if ($user instanceof Adherent) {
            $proposal->importAdherentData($user);
        }

        $form = $this->createForm(ProcurationProxyType::class, $proposal)
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            $manager = $this->getDoctrine()->getManager();
            $manager->persist($proposal);
            $manager->flush();

            return $this->redirectToRoute('app_procuration_proposal_thanks', [
                'uuid' => $referentUuid,
            ]);
        }

        return $this->render('procuration/proposal.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/je-propose/merci", defaults={"_enable_campaign_silence"=true}, name="app_procuration_proposal_thanks")
     * @Method("GET")
     */
    public function proposalThanksAction(Request $request): Response
    {
        return $this->render('procuration/proposal_thanks.html.twig', [
            'uuid' => $request->query->get('uuid'),
        ]);
    }

    /**
     * @Route("/ma-demande/{id}/{token}", defaults={"_enable_campaign_silence"=true}, requirements={"token": "%pattern_uuid%"}, name="app_procuration_my_request")
     * @Method("GET")
     */
    public function myRequestAction(ProcurationRequest $request, string $token): Response
    {
        if ($token !== $request->generatePrivateToken()) {
            throw $this->createNotFoundException();
        }

        return $this->render('procuration/my_request.html.twig', [
            'request' => $request,
            'proxy' => $request->getFoundProxy(),
        ]);
    }
}
