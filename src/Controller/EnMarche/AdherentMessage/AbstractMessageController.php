<?php

namespace App\Controller\EnMarche\AdherentMessage;

use App\AdherentMessage\AdherentMessageDataObject;
use App\AdherentMessage\AdherentMessageFactory;
use App\AdherentMessage\AdherentMessageManager;
use App\AdherentMessage\AdherentMessageStatusEnum;
use App\AdherentMessage\Filter\FilterFactory;
use App\AdherentMessage\Filter\FilterFormFactory;
use App\Controller\CanaryControllerTrait;
use App\Controller\EnMarche\AccessDelegatorTrait;
use App\Entity\Adherent;
use App\Entity\AdherentMessage\AbstractAdherentMessage;
use App\Entity\AdherentMessage\AdherentMessageInterface;
use App\Form\AdherentMessage\AdherentMessageType;
use App\Repository\AdherentMessageRepository;
use Doctrine\ORM\EntityManagerInterface as ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

abstract class AbstractMessageController extends AbstractController
{
    use AccessDelegatorTrait;
    use CanaryControllerTrait;

    private $templates = [
        'list' => 'message/list.html.twig',
        'send_success' => 'message/send_success/default.html.twig',
    ];

    #[Route(name: 'list', methods: ['GET'])]
    public function messageListAction(Request $request, AdherentMessageRepository $repository): Response
    {
        $this->checkAccess();

        $status = $request->query->get('status');

        if ($status && !AdherentMessageStatusEnum::isValid($status)) {
            throw new BadRequestHttpException('Invalid status');
        }

        $adherent = $this->getMainUser($request->getSession());

        return $this->renderTemplate($this->getTemplate('list'), [
            'messages' => $paginator = $repository->findAllByAuthor(
                $adherent,
                $this->getMessageType(),
                $status,
                $request->query->getInt('page', 1)
            ),
            'total_message_count' => $status ?
                $repository->countTotalMessage($adherent, $this->getMessageType()) :
                $paginator->getTotalItems(),
            'message_filter_status' => $status,
        ]);
    }

    #[Route(path: '/creer', name: 'create', methods: ['GET', 'POST'])]
    public function createMessageAction(Request $request, AdherentMessageManager $messageManager): Response
    {
        $this->checkAccess();

        $message = new AdherentMessageDataObject();

        if ($request->isMethod('POST') && $request->request->has('message_content')) {
            $message->setContent($request->request->get('message_content'));
        }

        $form = $this
            ->createForm(AdherentMessageType::class, $message)
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            $message = AdherentMessageFactory::create($this->getMainUser($request->getSession()), $form->getData(), $this->getMessageType());

            $messageManager->saveMessage($message);

            $this->addFlash('info', 'adherent_message.created_successfully');

            if ($form->get('next')->isClicked()) {
                return $this->redirectToMessageRoute('filter', ['uuid' => $message->getUuid()->toString()]);
            }

            return $this->redirectToMessageRoute('update', ['uuid' => $message->getUuid()]);
        }

        return $this->renderTemplate('message/create.html.twig', ['form' => $form->createView()]);
    }

    #[IsGranted('IS_AUTHOR_OF', subject: 'message')]
    #[Route(path: '/{uuid}/modifier', name: 'update', methods: ['GET', 'POST'])]
    public function updateMessageAction(
        Request $request,
        AbstractAdherentMessage $message,
        AdherentMessageManager $manager,
    ): Response {
        $this->checkAccess();

        if ($message->isSent()) {
            throw new BadRequestHttpException('This message has been already sent.');
        }

        $form = $this
            ->createForm(AdherentMessageType::class, $dataObject = AdherentMessageDataObject::createFromEntity($message))
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            $manager->updateMessage($message, $dataObject);

            $this->addFlash('info', 'adherent_message.updated_successfully');

            if ($form->get('next')->isClicked()) {
                return $this->redirectToMessageRoute('filter', ['uuid' => $message->getUuid()->toString()]);
            }

            return $this->redirectToMessageRoute('update', ['uuid' => $message->getUuid()]);
        }

        return $this->renderTemplate('message/update.html.twig', ['form' => $form->createView()]);
    }

    #[IsGranted('IS_AUTHOR_OF', subject: 'message')]
    #[Route(path: '/{uuid}/visualiser', name: 'preview', methods: ['GET'])]
    public function previewMessageAction(AbstractAdherentMessage $message): Response
    {
        $this->checkAccess();

        if (!$message->isSynchronized()) {
            throw new BadRequestHttpException('Message preview is not ready yet.');
        }

        return $this->renderTemplate('message/preview.html.twig', ['message' => $message]);
    }

    #[IsGranted('IS_AUTHOR_OF', subject: 'message')]
    #[Route(path: '/{uuid}/supprimer', name: 'delete', methods: ['GET'])]
    public function deleteMessageAction(AbstractAdherentMessage $message, ObjectManager $manager): Response
    {
        $this->checkAccess();

        $manager->remove($message);
        $manager->flush();

        $this->addFlash('info', 'adherent_message.deleted_successfully');

        return $this->redirectToMessageRoute('list');
    }

    #[IsGranted('IS_AUTHOR_OF', subject: 'message')]
    #[Route(path: '/{uuid}/filtrer', name: 'filter', methods: ['GET', 'POST'])]
    public function filterMessageAction(
        Request $request,
        AbstractAdherentMessage $message,
        FilterFormFactory $formFactory,
        AdherentMessageManager $manager,
    ): Response {
        $this->checkAccess();

        if ($message->isSent()) {
            throw new BadRequestHttpException('This message has been already sent.');
        }

        $adherent = $this->getMainUser($request->getSession());

        // Reset Filter object
        if ($request->query->has('reset') && $message->getFilter()) {
            $manager->updateFilter($message, FilterFactory::create($adherent, $message->getType()));

            return $this->redirectToMessageRoute('filter', ['uuid' => $message->getUuid()->toString()]);
        }

        $data = $message->getFilter() ?? FilterFactory::create($adherent, $message->getType());

        $form = $formFactory
            ->createForm($message->getType(), $data, $adherent)
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            $manager->updateFilter($message, $form->getData());

            $this->addFlash('info', 'adherent_message.filter_updated');

            return $this->redirectToMessageRoute('filter', ['uuid' => $message->getUuid()->toString()]);
        }

        return $this->renderTemplate($this->getMessageFilterTemplate($message), [
            'message' => $message,
            'form' => $form->createView(),
        ]);
    }

    #[IsGranted(new Expression("is_granted('IS_AUTHOR_OF', message) and is_granted('USER_CAN_SEND_MESSAGE', message)"))]
    #[Route(path: '/{uuid}/send', name: 'send', methods: ['GET'])]
    public function sendMessageAction(AbstractAdherentMessage $message, AdherentMessageManager $manager): Response
    {
        $this->checkAccess();

        if (!$message->isSynchronized()) {
            throw new BadRequestHttpException('The message is not yet ready to send.');
        }

        if (!$message->getRecipientCount()) {
            throw new BadRequestHttpException('Your message should have a filter');
        }

        if ($message->isSent()) {
            throw new BadRequestHttpException('This message has been already sent.');
        }

        $recipients = $this->getMessageRecipients($message);

        if (null !== $recipients && 0 === \count($recipients)) {
            $this->addFlash('error', 'Aucun destinataire de l\'email n\'a été trouvé.');

            return $this->redirectToMessageRoute('filter', ['uuid' => $message->getUuid()->toString()]);
        }

        if ($manager->send($message, (array) $recipients)) {
            $this->addFlash('info', 'adherent_message.campaign_sent_successfully');

            return $this->redirectToMessageRoute('send_success', ['uuid' => $message->getUuid()->toString()]);
        } else {
            $this->addFlash('error', 'adherent_message.campaign_sent_failure');
        }

        return $this->redirectToMessageRoute('list');
    }

    #[IsGranted(new Expression("is_granted('IS_AUTHOR_OF', message) and message.isSent()"))]
    #[Route(path: '/{uuid}/confirmation', name: 'send_success', methods: ['GET'])]
    public function sendSuccessAction(AbstractAdherentMessage $message): Response
    {
        $this->checkAccess();

        return $this->renderTemplate($this->getTemplate('send_success'), ['message' => $message]);
    }

    #[IsGranted('IS_AUTHOR_OF', subject: 'message')]
    #[Route(path: '/{uuid}/tester', name: 'test', methods: ['GET'])]
    public function sendTestMessageAction(AbstractAdherentMessage $message, AdherentMessageManager $manager): Response
    {
        $this->checkAccess();

        /** @var Adherent $user */
        $user = $this->getUser();

        if (!$message->isSynchronized()) {
            throw new BadRequestHttpException('The message is not yet ready to test sending.');
        }

        // we send test message to the current user, not the delegator if present
        if ($manager->sendTest($message, $user)) {
            $this->addFlash('info', 'adherent_message.test_campaign_sent_successfully');
        } else {
            $this->addFlash('info', 'adherent_message.test_campaign_sent_failure');
        }

        return $this->redirectToMessageRoute('filter', ['uuid' => $message->getUuid()->toString()]);
    }

    abstract protected function getMessageType(): string;

    protected function getMessageFilterTemplate(AbstractAdherentMessage $message): string
    {
        return 'message/filter/message_filter.html.twig';
    }

    protected function renderTemplate(string $template, array $parameters = []): Response
    {
        return $this->render($template, array_merge(
            $parameters,
            [
                'base_template' => \sprintf('message/_base_%s.html.twig', $messageType = $this->getMessageType()),
                'message_type' => $messageType,
            ]
        ));
    }

    protected function redirectToMessageRoute(string $subName, array $parameters = []): Response
    {
        return $this->redirectToRoute("app_message_{$this->getMessageType()}_$subName", $parameters);
    }

    protected function checkAccess(): void
    {
        if ($this->isCanary()) {
            $this->disableInProduction();
        }
    }

    protected function isCanary(): bool
    {
        return false;
    }

    protected function getMessageRecipients(AdherentMessageInterface $message): ?array
    {
        return null;
    }

    private function getTemplate(string $action): ?string
    {
        return $this->templates[$action] ?? null;
    }

    protected function setTemplate(string $action, string $template): void
    {
        $this->templates[$action] = $template;
    }
}
