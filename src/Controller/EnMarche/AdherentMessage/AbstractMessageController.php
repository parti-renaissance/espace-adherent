<?php

namespace AppBundle\Controller\EnMarche\AdherentMessage;

use AppBundle\AdherentMessage\AdherentMessageDataObject;
use AppBundle\AdherentMessage\AdherentMessageFactory;
use AppBundle\AdherentMessage\AdherentMessageStatusEnum;
use AppBundle\AdherentMessage\Command\CreateDefaultMessageFilterCommand;
use AppBundle\AdherentMessage\Filter\FilterFactory;
use AppBundle\AdherentMessage\Filter\FilterFormFactory;
use AppBundle\Controller\CanaryControllerTrait;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\AdherentMessage\AbstractAdherentMessage;
use AppBundle\Form\AdherentMessage\AdherentMessageType;
use AppBundle\Mailchimp\Manager;
use AppBundle\Repository\AdherentMessageRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

abstract class AbstractMessageController extends Controller
{
    use CanaryControllerTrait;

    /**
     * @Route(name="list", methods={"GET"})
     *
     * @param Adherent|UserInterface $adherent
     */
    public function messageListAction(
        Request $request,
        UserInterface $adherent,
        AdherentMessageRepository $repository
    ): Response {
        $this->disableInProduction();

        $status = $request->query->get('status');

        if ($status && !AdherentMessageStatusEnum::isValid($status)) {
            throw new BadRequestHttpException('Invalid status');
        }

        return $this->renderTemplate('message/list.html.twig', [
            'messages' => $repository->findAllByAuthor(
                $adherent,
                $status,
                $this->getMessageType(),
                $request->query->getInt('page', 1)
            ),
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
        MessageBusInterface $bus
    ): Response {
        $this->disableInProduction();

        $form = $this
            ->createForm(AdherentMessageType::class)
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            $message = AdherentMessageFactory::create($adherent, $form->getData(), $this->getMessageType());

            $manager->persist($message);

            $bus->dispatch(new CreateDefaultMessageFilterCommand($message));

            $manager->flush();

            $this->addFlash('info', 'adherent_message.created_successfully');

            if ($form->get('next')->isClicked()) {
                return $this->redirectToMessageRoute('filter', ['uuid' => $message->getUuid()->toString()]);
            }

            return $this->redirectToMessageRoute('update', ['uuid' => $message->getUuid()]);
        }

        return $this->renderTemplate('message/create.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route("/{uuid}/modifier", name="update", methods={"GET", "POST"})
     *
     * @Security("is_granted('IS_AUTHOR_OF', message)")
     */
    public function updateMessageAction(
        Request $request,
        AbstractAdherentMessage $message,
        ObjectManager $manager
    ): Response {
        $this->disableInProduction();

        if ($message->isSent()) {
            throw new BadRequestHttpException('This message has been already sent.');
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
                return $this->redirectToMessageRoute('filter', ['uuid' => $message->getUuid()->toString()]);
            }

            return $this->redirectToMessageRoute('update', ['uuid' => $message->getUuid()]);
        }

        return $this->renderTemplate('message/update.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route("/{uuid}/visualiser", name="preview", methods={"GET"})
     *
     * @Security("is_granted('IS_AUTHOR_OF', message)")
     */
    public function previewMessageAction(AbstractAdherentMessage $message): Response
    {
        $this->disableInProduction();

        if (!$message->isSynchronized()) {
            throw new BadRequestHttpException('Message preview is not ready yet.');
        }

        return $this->renderTemplate('message/preview.html.twig', ['message' => $message]);
    }

    /**
     * @Route("/{uuid}/supprimer", name="delete", methods={"GET"})
     *
     * @Security("is_granted('IS_AUTHOR_OF', message)")
     */
    public function deleteMessageAction(AbstractAdherentMessage $message, ObjectManager $manager): Response
    {
        $this->disableInProduction();

        $manager->remove($message);
        $manager->flush();

        $this->addFlash('info', 'adherent_message.deleted_successfully');

        return $this->redirectToMessageRoute('list');
    }

    /**
     * @Route("/{uuid}/content", name="content", methods={"GET"})
     *
     * @Security("is_granted('IS_AUTHOR_OF', message)")
     */
    public function getMessageTemplateAction(AbstractAdherentMessage $message, Manager $manager): Response
    {
        $this->disableInProduction();

        return new Response($manager->getCampaignContent($message));
    }

    /**
     * @Route("/{uuid}/filtrer", name="filter", methods={"GET", "POST"})
     *
     * @Security("is_granted('IS_AUTHOR_OF', message)")
     */
    public function filterMessageAction(
        Request $request,
        AbstractAdherentMessage $message,
        FilterFormFactory $formFactory,
        ObjectManager $manager
    ): Response {
        $this->disableInProduction();

        if ($message->isSent()) {
            throw new BadRequestHttpException('This message has been already sent.');
        }

        if ($message->hasReadOnlyFilter()) {
            return $this->renderTemplate("message/filter/{$message->getType()}.html.twig", ['message' => $message]);
        }

        // Reset Filter object
        if ($request->request->has('reset') && $message->getFilter()) {
            $message->resetFilter();
            $manager->flush();

            return $this->redirectToMessageRoute('filter', ['uuid' => $message->getUuid()->toString()]);
        }

        $data = $message->getFilter() ?? FilterFactory::create($this->getUser(), $message->getType());

        $form = $formFactory
            ->createForm($message->getType(), $data)
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            $message->setFilter($form->getData());
            $manager->flush();

            $this->addFlash('info', 'adherent_message.filter_updated');

            return $this->redirectToMessageRoute('filter', ['uuid' => $message->getUuid()->toString()]);
        }

        return $this->renderTemplate("message/filter/{$message->getType()}.html.twig", [
            'message' => $message,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{uuid}/send", name="send", methods={"GET"})
     *
     * @Security("is_granted('IS_AUTHOR_OF', message)")
     */
    public function sendMessageAction(
        AbstractAdherentMessage $message,
        Manager $manager,
        ObjectManager $entityManager
    ): Response {
        $this->disableInProduction();

        if (!$message->isSynchronized()) {
            throw new BadRequestHttpException('The message is not yet ready to send.');
        }

        if (!$message->getRecipientCount()) {
            throw new BadRequestHttpException('Your message should have a filter');
        }

        if ($message->isSent()) {
            throw new BadRequestHttpException('This message has been already sent.');
        }

        if ($manager->sendCampaign($message)) {
            $message->markAsSent();
            $entityManager->flush();

            $this->addFlash('info', 'adherent_message.campaign_sent_successfully');
        } else {
            $this->addFlash('info', 'adherent_message.campaign_sent_failure');
        }

        return $this->redirectToMessageRoute('list');
    }

    /**
     * @Route("/{uuid}/tester", name="test", methods={"GET"})
     *
     * @Security("is_granted('IS_AUTHOR_OF', message)")
     */
    public function sendTestMessageAction(AbstractAdherentMessage $message, Manager $manager): Response
    {
        $this->disableInProduction();

        if (!$message->isSynchronized()) {
            throw new BadRequestHttpException('The message is not yet ready to test sending.');
        }

        if ($manager->sendTestCampaign($message, [$this->getUser()->getEmailAddress()])) {
            $this->addFlash('info', 'adherent_message.test_campaign_sent_successfully');
        } else {
            $this->addFlash('info', 'adherent_message.test_campaign_sent_failure');
        }

        return $this->redirectToMessageRoute('preview', ['uuid' => $message->getUuid()->toString()]);
    }

    abstract protected function getMessageType(): string;

    protected function renderTemplate(string $template, array $parameters = []): Response
    {
        return $this->render($template, array_merge(
            $parameters,
            [
                'base_template' => sprintf('message/_base_%s.html.twig', $messageType = $this->getMessageType()),
                'message_type' => $messageType,
            ]
        ));
    }

    protected function redirectToMessageRoute(string $subName, array $parameters = []): Response
    {
        return $this->redirectToRoute("app_message_{$this->getMessageType()}_${subName}", $parameters);
    }
}
