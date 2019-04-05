<?php

namespace AppBundle\Controller\EnMarche\AdherentMessage;

use AppBundle\AdherentMessage\AdherentMessageDataObject;
use AppBundle\AdherentMessage\AdherentMessageFactory;
use AppBundle\AdherentMessage\AdherentMessageStatusEnum;
use AppBundle\AdherentMessage\AdherentMessageTypeEnum;
use AppBundle\AdherentMessage\Filter\FilterFormFactory;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\AdherentMessage\AbstractAdherentMessage;
use AppBundle\Entity\AdherentMessage\Filter\CommitteeFilter;
use AppBundle\Entity\Committee;
use AppBundle\Form\AdherentMessage\AdherentMessageType;
use AppBundle\Mailchimp\Manager;
use AppBundle\Repository\AdherentMessageRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @Route(path="/espace-animateur/{committee_slug}/messagerie", name="app_message_committee_")
 *
 * @ParamConverter("committee", options={"mapping": {"committee_slug": "slug"}})
 *
 * @Security("is_granted('ROLE_HOST') or is_granted('ROLE_SUPERVISOR')")
 */
class CommitteeMessageController extends AbstractMessageController
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
        Committee $committee = null
    ): Response {
        $this->disableInProduction();

        $status = $request->query->get('status');

        if ($status && !AdherentMessageStatusEnum::isValid($status)) {
            throw new BadRequestHttpException('Invalid status');
        }

        return $this->renderTemplate('message/list.html.twig', [
            'messages' => $repository->findAllCommitteeMessage($adherent, $committee, $status),
            'committee' => $committee,
            'route_params' => ['committee_slug' => $committee->getSlug()],
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
        ObjectManager $manager,
        MessageBusInterface $bus,
        Committee $committee = null
    ): Response {
        $this->disableInProduction();

        $form = $this
            ->createForm(AdherentMessageType::class)
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            $message = AdherentMessageFactory::create($adherent, $form->getData(), $this->getMessageType());
            $message->setFilter(new CommitteeFilter($committee));

            $manager->persist($message);

            $manager->flush();

            $this->addFlash('info', 'adherent_message.created_successfully');

            if ($form->get('next')->isClicked()) {
                return $this->redirectToMessageRoute('filter', [
                    'uuid' => $message->getUuid()->toString(),
                    'committee_slug' => $committee->getSlug(),
                ]);
            }

            return $this->redirectToMessageRoute('update', [
                'uuid' => $message->getUuid(),
                'committee_slug' => $committee->getSlug(),
            ]);
        }

        return $this->renderTemplate('message/create.html.twig', [
            'form' => $form->createView(),
            'committee' => $committee,
            'route_params' => ['committee_slug' => $committee->getSlug()],
        ]);
    }

    /**
     * @Route("/{uuid}/modifier", requirements={"uuid": "%pattern_uuid%"}, name="update", methods={"GET", "POST"})
     *
     * @Security("is_granted('IS_AUTHOR_OF', message)")
     */
    public function updateMessageAction(
        Request $request,
        AbstractAdherentMessage $message,
        ObjectManager $manager,
        Committee $committee = null
    ): Response {
        $this->disableInProduction();

        if ($message->isSent()) {
            throw new BadRequestHttpException('This message has already been sent.');
        }

        $form = $this
            ->createForm(AdherentMessageType::class, $dataObject = AdherentMessageDataObject::createFromEntity($message))
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            $message->updateFromDataObject($dataObject);

            $manager->flush();

            $this->addFlash('info', 'adherent_message.updated_successfully');

            if ($form->get('next')->isClicked()) {
                return $this->redirectToMessageRoute('filter', [
                    'uuid' => $message->getUuid()->toString(),
                    'committee_slug' => $committee->getSlug(),
                ]);
            }

            return $this->redirectToMessageRoute('update', [
                'uuid' => $message->getUuid(),
                'committee_slug' => $committee->getSlug(),
            ]);
        }

        return $this->renderTemplate('message/update.html.twig', [
            'form' => $form->createView(),
            'committee' => $committee,
            'route_params' => ['committee_slug' => $committee->getSlug()],
        ]);
    }

    /**
     * @Route("/{uuid}/filtrer", name="filter", methods={"GET"})
     *
     * @Security("is_granted('IS_AUTHOR_OF', message)")
     */
    public function filterMessageAction(
        Request $request,
        AbstractAdherentMessage $message,
        FilterFormFactory $formFactory,
        ObjectManager $manager,
        Committee $committee = null
    ): Response {
        $this->disableInProduction();

        if ($message->isSent()) {
            throw new BadRequestHttpException('This message has already been sent.');
        }

        return $this->renderTemplate('message/filter/committee.html.twig', [
            'message' => $message,
            'committee' => $committee,
            'route_params' => ['committee_slug' => $committee->getSlug()],
        ]);
    }

    /**
     * @Route("/{uuid}/visualiser", name="preview", methods={"GET"})
     *
     * @Security("is_granted('IS_AUTHOR_OF', message)")
     */
    public function previewMessageAction(AbstractAdherentMessage $message, Committee $committee = null): Response
    {
        $this->disableInProduction();

        if (!$message->isSynchronized()) {
            throw new BadRequestHttpException('Message preview is not ready yet.');
        }

        return $this->renderTemplate('message/preview.html.twig', [
            'message' => $message,
            'committee' => $committee,
            'route_params' => ['committee_slug' => $committee->getSlug()],
        ]);
    }

    /**
     * @Route("/{uuid}/supprimer", name="delete", methods={"GET"})
     *
     * @Security("is_granted('IS_AUTHOR_OF', message)")
     */
    public function deleteMessageAction(
        AbstractAdherentMessage $message,
        ObjectManager $manager,
        Committee $committee = null
    ): Response {
        $this->disableInProduction();

        $manager->remove($message);
        $manager->flush();

        $this->addFlash('info', 'adherent_message.deleted_successfully');

        return $this->redirectToMessageRoute('list', ['committee_slug' => $committee->getSlug()]);
    }

    /**
     * @Route("/{uuid}/send", name="send", methods={"GET"})
     *
     * @Security("is_granted('IS_AUTHOR_OF', message)")
     */
    public function sendMessageAction(
        AbstractAdherentMessage $message,
        Manager $manager,
        ObjectManager $entityManager,
        Committee $committee = null
    ): Response {
        $this->disableInProduction();

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
            $entityManager->flush();

            $this->addFlash('info', 'adherent_message.campaign_sent_successfully');
        } else {
            $this->addFlash('info', 'adherent_message.campaign_sent_failure');
        }

        return $this->redirectToMessageRoute('list', ['committee_slug' => $committee->getSlug()]);
    }

    protected function getMessageType(): string
    {
        return AdherentMessageTypeEnum::COMMITTEE;
    }
}
