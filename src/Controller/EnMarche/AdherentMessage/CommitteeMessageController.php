<?php

namespace AppBundle\Controller\EnMarche\AdherentMessage;

use AppBundle\AdherentMessage\AdherentMessageFactory;
use AppBundle\AdherentMessage\AdherentMessageManager;
use AppBundle\AdherentMessage\AdherentMessageStatusEnum;
use AppBundle\AdherentMessage\AdherentMessageTypeEnum;
use AppBundle\AdherentMessage\CommitteeAdherentMessageDataObject;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\AdherentMessage\CommitteeAdherentMessage;
use AppBundle\Entity\AdherentMessage\Filter\CommitteeFilter;
use AppBundle\Entity\Committee;
use AppBundle\Entity\CommitteeFeedItem;
use AppBundle\Form\AdherentMessage\CommitteeAdherentMessageType;
use AppBundle\Mailchimp\Manager;
use AppBundle\Repository\AdherentMessageRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @Route(path="/espace-animateur/{committee_slug}/messagerie", name="app_message_committee_")
 *
 * @ParamConverter("committee", options={"mapping": {"committee_slug": "slug"}})
 *
 * @Security("is_granted('HOST_COMMITTEE', committee) and committee.isApproved()")
 */
class CommitteeMessageController extends Controller
{
    /**
     * @Route(name="list", methods={"GET"})
     *
     * @param Adherent|UserInterface $adherent
     */
    public function messageListAction(
        Request $request,
        UserInterface $adherent,
        AdherentMessageRepository $repository,
        Committee $committee
    ): Response {
        $status = $request->query->get('status');

        if ($status && !AdherentMessageStatusEnum::isValid($status)) {
            throw new BadRequestHttpException('Invalid status');
        }

        return $this->renderTemplate('message/list.html.twig', $committee, [
            'messages' => $paginator = $repository->findAllCommitteeMessage(
                $adherent,
                $committee,
                $status,
                $request->query->getInt('page', 1)
            ),
            'total_message_count' => $status ?
                $repository->countTotalCommitteeMessage($adherent, $committee) :
                $paginator->getTotalItems(),
            'message_filter_status' => $status,
        ]);
    }

    /**
     * @Route("/creer", name="create", methods={"GET", "POST"})
     *
     * @param Adherent|UserInterface $adherent
     */
    public function createMessageAction(
        Request $request,
        UserInterface $adherent,
        Committee $committee,
        AdherentMessageManager $manager
    ): Response {
        $form = $this
            ->createForm(CommitteeAdherentMessageType::class)
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            $message = AdherentMessageFactory::create(
                $adherent,
                $command = $form->getData(),
                AdherentMessageTypeEnum::COMMITTEE
            );
            $message->setFilter(new CommitteeFilter($committee));

            $manager->saveMessage($message);

            $this->addFlash('info', 'adherent_message.created_successfully');

            if ($form->get('next')->isClicked()) {
                return $this->redirectToRoute('app_message_committee_filter', [
                    'uuid' => $message->getUuid()->toString(),
                    'committee_slug' => $committee->getSlug(),
                ]);
            }

            return $this->redirectToRoute('app_message_committee_update', [
                'uuid' => $message->getUuid(),
                'committee_slug' => $committee->getSlug(),
            ]);
        }

        return $this->renderTemplate('message/committee_create.html.twig', $committee, ['form' => $form->createView()]);
    }

    /**
     * @Route("/{uuid}/modifier", requirements={"uuid": "%pattern_uuid%"}, name="update", methods={"GET", "POST"})
     *
     * @Security("is_granted('IS_AUTHOR_OF', message)")
     */
    public function updateMessageAction(
        Request $request,
        CommitteeAdherentMessage $message,
        ObjectManager $manager,
        Committee $committee
    ): Response {
        if ($message->isSent()) {
            throw new BadRequestHttpException('This message has already been sent.');
        }

        $form = $this
            ->createForm(
                CommitteeAdherentMessageType::class,
                $dataObject = CommitteeAdherentMessageDataObject::createFromEntity($message)
            )
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            $message->updateFromDataObject($dataObject);

            $manager->flush();

            $this->addFlash('info', 'adherent_message.updated_successfully');

            if ($form->get('next')->isClicked()) {
                return $this->redirectToRoute('app_message_committee_filter', [
                    'uuid' => $message->getUuid()->toString(),
                    'committee_slug' => $committee->getSlug(),
                ]);
            }

            return $this->redirectToRoute('app_message_committee_update', [
                'uuid' => $message->getUuid(),
                'committee_slug' => $committee->getSlug(),
            ]);
        }

        return $this->renderTemplate('message/committee_update.html.twig', $committee, ['form' => $form->createView()]);
    }

    /**
     * @Route("/{uuid}/filtrer", name="filter", methods={"GET"})
     *
     * @Security("is_granted('IS_AUTHOR_OF', message)")
     */
    public function filterMessageAction(CommitteeAdherentMessage $message, Committee $committee): Response
    {
        if ($message->isSent()) {
            throw new BadRequestHttpException('This message has already been sent.');
        }

        return $this->renderTemplate('message/filter/committee.html.twig', $committee, ['message' => $message]);
    }

    /**
     * @Route("/{uuid}/visualiser", name="preview", methods={"GET"})
     *
     * @Security("is_granted('IS_AUTHOR_OF', message)")
     */
    public function previewMessageAction(CommitteeAdherentMessage $message, Committee $committee): Response
    {
        if (!$message->isSynchronized()) {
            throw new BadRequestHttpException('Message preview is not ready yet.');
        }

        return $this->renderTemplate('message/preview.html.twig', $committee, ['message' => $message]);
    }

    /**
     * @Route("/{uuid}/supprimer", name="delete", methods={"GET"})
     *
     * @Security("is_granted('IS_AUTHOR_OF', message)")
     */
    public function deleteMessageAction(
        CommitteeAdherentMessage $message,
        ObjectManager $manager,
        Committee $committee
    ): Response {
        $manager->remove($message);
        $manager->flush();

        $this->addFlash('info', 'adherent_message.deleted_successfully');

        return $this->redirectToRoute('app_message_committee_list', ['committee_slug' => $committee->getSlug()]);
    }

    /**
     * @Route("/{uuid}/send", name="send", methods={"GET"})
     *
     * @Security("is_granted('IS_AUTHOR_OF', message)")
     */
    public function sendMessageAction(
        CommitteeAdherentMessage $message,
        Manager $manager,
        ObjectManager $entityManager,
        Committee $committee
    ): Response {
        if (!$message->isSynchronized()) {
            throw new BadRequestHttpException('The message is not ready to send yet.');
        }

        if (!$message->getRecipientCount()) {
            throw new BadRequestHttpException('Your message should have a filter');
        }

        if ($message->isSent()) {
            throw new BadRequestHttpException('This message has already been sent.');
        }

        if ($manager->sendCampaign($message)) {
            $message->markAsSent();

            if ($message->isSendToTimeline()) {
                $entityManager->persist(CommitteeFeedItem::createMessage(
                    $committee,
                    $message->getAuthor(),
                    $message->getContent()
                ));
            }

            $entityManager->flush();

            $this->addFlash('info', 'adherent_message.campaign_sent_successfully');
        } else {
            $this->addFlash('info', 'adherent_message.campaign_sent_failure');
        }

        return $this->redirectToRoute('app_message_committee_list', ['committee_slug' => $committee->getSlug()]);
    }

    /**
     * @Route("/{uuid}/tester", name="test", methods={"GET"})
     *
     * @Security("is_granted('IS_AUTHOR_OF', message)")
     */
    public function sendTestMessageAction(
        CommitteeAdherentMessage $message,
        Manager $manager,
        Committee $committee
    ): Response {
        if (!$message->isSynchronized()) {
            throw new BadRequestHttpException('The message is not yet ready to test sending.');
        }

        if ($manager->sendTestCampaign($message, [$this->getUser()->getEmailAddress()])) {
            $this->addFlash('info', 'adherent_message.test_campaign_sent_successfully');
        } else {
            $this->addFlash('info', 'adherent_message.test_campaign_sent_failure');
        }

        return $this->redirectToRoute('app_message_committee_preview', [
            'committee_slug' => $committee->getSlug(),
            'uuid' => $message->getUuid()->toString(),
        ]);
    }

    private function renderTemplate(string $template, Committee $committee, array $parameters = []): Response
    {
        return $this->render($template, array_merge(
            $parameters,
            [
                'committee' => $committee,
                'route_params' => ['committee_slug' => $committee->getSlug()],
                'base_template' => 'message/_base_committee.html.twig',
                'message_type' => AdherentMessageTypeEnum::COMMITTEE,
            ]
        ));
    }
}
