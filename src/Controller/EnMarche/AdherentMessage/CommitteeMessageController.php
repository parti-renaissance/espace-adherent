<?php

namespace App\Controller\EnMarche\AdherentMessage;

use App\AdherentMessage\AdherentMessageDataObject;
use App\AdherentMessage\AdherentMessageFactory;
use App\AdherentMessage\AdherentMessageManager;
use App\AdherentMessage\AdherentMessageStatusEnum;
use App\AdherentMessage\AdherentMessageTypeEnum;
use App\AdherentMessage\Filter\FilterFormFactory;
use App\Entity\Adherent;
use App\Entity\AdherentMessage\AbstractAdherentMessage;
use App\Entity\AdherentMessage\CommitteeAdherentMessage;
use App\Entity\AdherentMessage\Filter\MessageFilter;
use App\Entity\Committee;
use App\Form\AdherentMessage\AdherentMessageType;
use App\Mailchimp\Manager;
use App\Repository\AdherentMessageRepository;
use Doctrine\ORM\EntityManagerInterface as ObjectManager;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('HOST_COMMITTEE', subject: 'committee')]
#[Route(path: '/espace-animateur/{committee_slug}/messagerie', name: 'app_message_committee_')]
class CommitteeMessageController extends AbstractController
{
    #[Route(name: 'list', methods: ['GET'])]
    public function messageListAction(
        Request $request,
        AdherentMessageRepository $repository,
        #[MapEntity(mapping: ['committee_slug' => 'slug'])]
        Committee $committee,
    ): Response {
        /** @var Adherent $adherent */
        $adherent = $this->getUser();

        $status = $request->query->get('status');

        if ($status && !AdherentMessageStatusEnum::isValid($status)) {
            throw new BadRequestHttpException('Invalid status');
        }

        return $this->renderTemplate('message/list_committee.html.twig', $committee, [
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

    #[Route(path: '/creer', name: 'create', methods: ['GET', 'POST'])]
    public function createMessageAction(
        Request $request,
        #[MapEntity(mapping: ['committee_slug' => 'slug'])]
        Committee $committee,
        AdherentMessageManager $manager,
    ): Response {
        /** @var Adherent $adherent */
        $adherent = $this->getUser();

        $form = $this
            ->createForm(AdherentMessageType::class)
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            $message = AdherentMessageFactory::create(
                $adherent,
                $form->getData(),
                AdherentMessageTypeEnum::COMMITTEE
            );
            $message->setFilter($filter = new MessageFilter());
            $filter->setCommittee($committee);
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

        return $this->renderTemplate('message/create.html.twig', $committee, ['form' => $form->createView()]);
    }

    #[IsGranted('IS_AUTHOR_OF', subject: 'message')]
    #[Route(path: '/{uuid}/modifier', requirements: ['uuid' => '%pattern_uuid%'], name: 'update', methods: ['GET', 'POST'])]
    public function updateMessageAction(
        Request $request,
        CommitteeAdherentMessage $message,
        AdherentMessageManager $manager,
        #[MapEntity(mapping: ['committee_slug' => 'slug'])]
        Committee $committee,
    ): Response {
        if ($message->isSent()) {
            throw new BadRequestHttpException('This message has already been sent.');
        }

        $form = $this
            ->createForm(AdherentMessageType::class, $dataObject = AdherentMessageDataObject::createFromEntity($message))
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            $manager->updateMessage($message, $dataObject);

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

        return $this->renderTemplate('message/update.html.twig', $committee, ['form' => $form->createView()]);
    }

    #[IsGranted('IS_AUTHOR_OF', subject: 'message')]
    #[Route(path: '/{uuid}/filtrer', name: 'filter', methods: ['GET', 'POST'])]
    public function filterMessageAction(
        Request $request,
        CommitteeAdherentMessage $message,
        #[MapEntity(mapping: ['committee_slug' => 'slug'])]
        Committee $committee,
        AdherentMessageManager $manager,
        FilterFormFactory $formFactory,
    ): Response {
        /** @var Adherent $adherent */
        $adherent = $this->getUser();

        if ($message->isSent()) {
            throw new BadRequestHttpException('This message has been already sent.');
        }

        // Reset Filter object
        if ($request->query->has('reset') && $message->getFilter()) {
            $manager->updateFilter($message, $filter = new MessageFilter());
            $filter->setCommittee($committee);

            return $this->redirectToRoute('app_message_committee_filter', [
                'uuid' => $message->getUuid()->toString(),
                'committee_slug' => $committee->getSlug(),
            ]);
        }

        $filter = $message->getFilter() ?? new MessageFilter();
        $filter->setCommittee($committee);

        $form = $formFactory
            ->createForm($message->getType(), $filter, $adherent)
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            $manager->updateFilter($message, $filter);

            $this->addFlash('info', 'adherent_message.filter_updated');

            return $this->redirectToRoute('app_message_committee_filter', [
                'uuid' => $message->getUuid()->toString(),
                'committee_slug' => $committee->getSlug(),
            ]);
        }

        return $this->renderTemplate('message/filter/message_filter.html.twig', $committee, [
            'message' => $message,
            'form' => $form->createView(),
        ]);
    }

    #[IsGranted('IS_AUTHOR_OF', subject: 'message')]
    #[Route(path: '/{uuid}/visualiser', name: 'preview', methods: ['GET'])]
    public function previewMessageAction(
        CommitteeAdherentMessage $message,
        #[MapEntity(mapping: ['committee_slug' => 'slug'])]
        Committee $committee,
    ): Response {
        if (!$message->isSynchronized()) {
            throw new BadRequestHttpException('Message preview is not ready yet.');
        }

        return $this->renderTemplate('message/preview.html.twig', $committee, ['message' => $message]);
    }

    #[IsGranted('IS_AUTHOR_OF', subject: 'message')]
    #[Route(path: '/{uuid}/supprimer', name: 'delete', methods: ['GET'])]
    public function deleteMessageAction(
        CommitteeAdherentMessage $message,
        ObjectManager $manager,
        #[MapEntity(mapping: ['committee_slug' => 'slug'])]
        Committee $committee,
    ): Response {
        $manager->remove($message);
        $manager->flush();

        $this->addFlash('info', 'adherent_message.deleted_successfully');

        return $this->redirectToRoute('app_message_committee_list', ['committee_slug' => $committee->getSlug()]);
    }

    #[IsGranted('IS_AUTHOR_OF', subject: 'message')]
    #[Route(path: '/{uuid}/send', name: 'send', methods: ['GET'])]
    public function sendMessageAction(
        CommitteeAdherentMessage $message,
        AdherentMessageManager $manager,
        #[MapEntity(mapping: ['committee_slug' => 'slug'])]
        Committee $committee,
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

        if ($manager->send($message)) {
            $this->addFlash('info', 'adherent_message.campaign_sent_successfully');

            return $this->redirectToRoute('app_message_committee_send_success', ['committee_slug' => $committee->getSlug(), 'uuid' => $message->getUuid()->toString()]);
        } else {
            $this->addFlash('error', 'adherent_message.campaign_sent_failure');
        }

        return $this->redirectToRoute('app_message_committee_list', ['committee_slug' => $committee->getSlug()]);
    }

    #[IsGranted(new Expression("is_granted('IS_AUTHOR_OF', subject) and subject.isSent()"), subject: 'message')]
    #[Route(path: '/{uuid}/confirmation', name: 'send_success', methods: ['GET'])]
    public function sendSuccessAction(
        AbstractAdherentMessage $message,
        #[MapEntity(mapping: ['committee_slug' => 'slug'])]
        Committee $committee,
    ): Response {
        return $this->renderTemplate('message/send_success/committee.html.twig', $committee, ['message' => $message]);
    }

    #[IsGranted('IS_AUTHOR_OF', subject: 'message')]
    #[Route(path: '/{uuid}/tester', name: 'test', methods: ['GET'])]
    public function sendTestMessageAction(
        CommitteeAdherentMessage $message,
        Manager $manager,
        #[MapEntity(mapping: ['committee_slug' => 'slug'])]
        Committee $committee,
    ): Response {
        if (!$message->isSynchronized()) {
            throw new BadRequestHttpException('The message is not yet ready to test sending.');
        }

        if ($manager->sendTestCampaign($message, [$this->getUser()->getEmailAddress()])) {
            $this->addFlash('info', 'adherent_message.test_campaign_sent_successfully');
        } else {
            $this->addFlash('info', 'adherent_message.test_campaign_sent_failure');
        }

        return $this->redirectToRoute('app_message_committee_filter', [
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
