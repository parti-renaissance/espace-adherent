<?php

namespace App\Controller\EnMarche\CommitteeDesignation;

use App\Entity\Adherent;
use App\Entity\Committee;
use App\Entity\CommitteeElection;
use App\Entity\VotingPlatform\Designation\Designation;
use App\Form\PartialDesignationType;
use App\Mailer\MailerService;
use App\Mailer\Message\VotingPlatformPartialElectionIsOpenMessage;
use App\VotingPlatform\Command\NotifyPartialElectionVoterCommand;
use App\VotingPlatform\Designation\CreatePartialDesignationCommand;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @ParamConverter("committee", options={"mapping": {"committee_slug": "slug"}})
 * @Security("is_granted('ROLE_REFERENT') or is_granted('ROLE_DELEGATED_REFERENT')")
 */
#[Route(path: '/espace-referent/comites/{committee_slug}/designations', name: 'app_referent_designations')]
class ReferentDesignationController extends AbstractDesignationController
{
    /**
     * @IsGranted("MANAGE_ZONEABLE_ITEM__REFERENT", subject="committee")
     */
    #[Route(path: '/creer-une-partielle', name: '_create_partial', methods: ['GET', 'POST'])]
    public function createPartialAction(
        Request $request,
        Committee $committee,
        MailerService $transactionalMailer,
        EntityManagerInterface $entityManager
    ): Response {
        /** @var Adherent $adherent */
        $adherent = $this->getUser();
        $type = $request->query->get('type');

        if (empty($type)) {
            throw new BadRequestHttpException('Type of election is empty');
        }

        $command = new CreatePartialDesignationCommand($committee, $type, $request->query->get('pool'));

        $form = $this
            ->createForm(PartialDesignationType::class, $command)
            ->handleRequest($request)
        ;

        $step = 'form';
        $messageContent = null;

        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('next')->isClicked() || $form->get('confirm')->isClicked()) {
                $designation = Designation::createPartialFromCommand($command);

                if ($form->get('next')->isClicked()) {
                    $step = 'confirm';
                    $messageContent = $transactionalMailer->renderMessage(VotingPlatformPartialElectionIsOpenMessage::create(
                        $designation,
                        $command->getMessage(),
                        [$adherent],
                    ));
                } elseif ($form->get('confirm')->isClicked()) {
                    $committee->setCurrentElection(new CommitteeElection($designation));

                    $entityManager->flush();

                    $this->dispatchMessage(new NotifyPartialElectionVoterCommand($committee->getId()));

                    $this->addFlash('info', $designation->getDenomination(true, true).' partielle a bien été créée.');

                    return $this->redirectToRoute('app_referent_committees_designations_partials');
                }
            }
        }

        return $this->renderTemplate('committee_designation/create_partial.html.twig', $request, [
            'form' => $form->createView(),
            'command' => $command,
            'message_content' => $messageContent,
            'step' => $step,
        ]);
    }

    protected function getSpaceType(): string
    {
        return 'referent';
    }
}
