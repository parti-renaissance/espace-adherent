<?php

namespace AppBundle\Controller\EnMarche;

use AppBundle\AdherentMessage\AdherentMessageDataObject;
use AppBundle\AdherentMessage\AdherentMessageFactory;
use AppBundle\AdherentMessage\AdherentMessageStatusEnum;
use AppBundle\AdherentMessage\Filter\FilterFactory;
use AppBundle\AdherentMessage\Filter\FilterFormFactory;
use AppBundle\Controller\CanaryControllerTrait;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\AdherentMessage\AbstractAdherentMessage;
use AppBundle\Form\AdherentMessageType;
use AppBundle\Mailchimp\Manager;
use AppBundle\Repository\AdherentMessageRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @Route("/{prefix}messagerie", defaults={"prefix": ""}, requirements={"prefix": "|espace-referent/"})
 *
 * @Security("is_granted('ROLE_ADHERENT_MESSAGE')")
 */
class MessageController extends Controller
{
    use CanaryControllerTrait;

    /**
     * @Route(name="app_message_list", methods={"GET"})
     *
     * @param Adherent|UserInterface $adherent
     */
    public function messageListAction(
        string $prefix,
        Request $request,
        UserInterface $adherent,
        AdherentMessageRepository $repository
    ): Response {
        $this->disableInProduction();

        $status = $request->query->get('status');

        if ($status && !AdherentMessageStatusEnum::isValid($status)) {
            throw new BadRequestHttpException('Invalid status');
        }

        return $this->renderTemplate(
            $prefix,
            'message/list.html.twig',
            ['messages' => $repository->findAllByAuthor($adherent, $status)]
        );
    }

    /**
     * @Route("/creer", name="app_message_create", methods={"GET", "POST"})
     *
     * @param Adherent|UserInterface $adherent
     */
    public function createMessageAction(
        string $prefix,
        Request $request,
        UserInterface $adherent,
        ObjectManager $manager
    ): Response {
        $this->disableInProduction();

        $form = $this
            ->createForm(AdherentMessageType::class)
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            $message = AdherentMessageFactory::create($adherent, $form->getData());

            $manager->persist($message);
            $manager->flush();

            $this->addFlash('info', 'adherent_message.created_successfully');

            if ($form->get('next')->isClicked()) {
                return $this->redirectToRoute(
                    'app_message_filter',
                    [
                        'prefix' => $prefix,
                        'uuid' => $message->getUuid()->toString(),
                    ]
                );
            }

            return $this->redirectToRoute(
                'app_message_update',
                [
                    'prefix' => $prefix,
                    'uuid' => $message->getUuid(),
                ]
            );
        }

        return $this->renderTemplate($prefix, 'message/create.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route("/{uuid}/modifier", name="app_message_update", methods={"GET", "POST"})
     *
     * @Security("is_granted('IS_AUTHOR_OF', message)")
     */
    public function updateMessageAction(
        string $prefix,
        Request $request,
        AbstractAdherentMessage $message,
        ObjectManager $manager
    ): Response {
        $this->disableInProduction();

        if ($message->isSent()) {
            throw new BadRequestHttpException('This message has been already sent.');
        }

        $form = $this
            ->createForm(
                AdherentMessageType::class,
                $dataObject = AdherentMessageDataObject::createFromEntity($message),
                ['is_creation' => false]
            )
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            $message->updateFromDataObject($dataObject);

            $manager->flush();

            $this->addFlash('info', 'adherent_message.updated_successfully');

            if ($form->get('next')->isClicked()) {
                return $this->redirectToRoute(
                    'app_message_filter',
                    [
                        'prefix' => $prefix,
                        'uuid' => $message->getUuid()->toString(),
                    ]
                );
            }

            return $this->redirectToRoute('app_message_update', ['prefix' => $prefix, 'uuid' => $message->getUuid()]);
        }

        return $this->renderTemplate($prefix, 'message/update.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route("/{uuid}/visualiser", name="app_message_preview", methods={"GET"})
     *
     * @Security("is_granted('IS_AUTHOR_OF', message)")
     */
    public function previewMessageAction(string $prefix, AbstractAdherentMessage $message): Response
    {
        $this->disableInProduction();

        if (!$message->isSynchronized()) {
            throw new BadRequestHttpException('Message preview is not ready yet.');
        }

        return $this->renderTemplate($prefix, 'message/preview.html.twig', ['message' => $message]);
    }

    /**
     * @Route("/{uuid}/supprimer", name="app_message_delete", methods={"GET"})
     *
     * @Security("is_granted('IS_AUTHOR_OF', message)")
     */
    public function deleteMessageAction(string $prefix, AbstractAdherentMessage $message, ObjectManager $manager): Response
    {
        $this->disableInProduction();

        $manager->remove($message);
        $manager->flush();

        $this->addFlash('info', 'adherent_message.deleted_successfully');

        return $this->redirectToRoute('app_message_list', ['prefix' => $prefix]);
    }

    /**
     * @Route("/{uuid}/content", name="app_message_content", methods={"GET"})
     *
     * @Security("is_granted('IS_AUTHOR_OF', message)")
     */
    public function getMessageTemplateAction(AbstractAdherentMessage $message, Manager $manager): Response
    {
        $this->disableInProduction();

        return new Response($manager->getCampaignContent($message));
    }

    /**
     * @Route("/{uuid}/filtrer", name="app_message_filter", methods={"GET", "POST"})
     *
     * @Security("is_granted('IS_AUTHOR_OF', message)")
     */
    public function filterMessageAction(
        string $prefix,
        Request $request,
        AbstractAdherentMessage $message,
        FilterFormFactory $formFactory,
        ObjectManager $manager
    ): Response {
        $this->disableInProduction();

        if ($message->isSent()) {
            throw new BadRequestHttpException('This message has been already sent.');
        }

        // Reset Filter object
        if ($request->request->has('reset') && $message->getFilter()) {
            $message->resetFilter();
            $manager->flush();

            return $this->redirectToRoute('app_message_filter', [
                'prefix' => $prefix,
                'uuid' => $message->getUuid()->toString(),
            ]);
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

            return $this->redirectToRoute('app_message_filter', [
                'prefix' => $prefix,
                'uuid' => $message->getUuid()->toString(),
            ]);
        }

        return $this->renderTemplate($prefix, 'message/filter.html.twig', [
            'message' => $message,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{uuid}/send", name="app_message_send", methods={"GET"})
     *
     * @Security("is_granted('IS_AUTHOR_OF', message)")
     */
    public function sendMessageAction(
        string $prefix,
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

        return $this->redirectToRoute('app_message_list', ['prefix' => $prefix]);
    }

    private function renderTemplate(string $uriPrefix, string $template, array $parameters = []): Response
    {
        switch (rtrim($uriPrefix, '/')) {
            case 'espace-referent':
                $baseTemplate = 'message/_base_referent.html.twig';
                break;

            default:
                $baseTemplate = 'message/_base.html.twig';
                break;
        }

        return $this->render(
            $template,
            array_merge(
                $parameters,
                [
                    'base_template' => $baseTemplate,
                    'route_params' => ['prefix' => $uriPrefix],
                ]
            )
        );
    }
}
