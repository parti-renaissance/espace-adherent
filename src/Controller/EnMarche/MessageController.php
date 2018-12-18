<?php

namespace AppBundle\Controller\EnMarche;

use AppBundle\AdherentMessage\AdherentMessageDataObject;
use AppBundle\AdherentMessage\Factory;
use AppBundle\AdherentMessage\Filter\FilterFactory;
use AppBundle\AdherentMessage\Filter\ReferentFilterDataObject;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\AdherentMessage\AbstractAdherentMessage;
use AppBundle\Form\AdherentMessageType;
use AppBundle\Mailchimp\Manager;
use AppBundle\Repository\AdherentMessageRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @Route("/{prefix}messagerie", defaults={"prefix": ""}, requirements={"prefix": "|espace-referent/"})
 *
 * @Security("is_granted('ROLE_ADHERENT_MESSAGE')")
 */
class MessageController extends AbstractController
{
    /**
     * @Route(name="app_message_list", methods={"GET"})
     */
    public function messageListAction(string $prefix, UserInterface $adherent, AdherentMessageRepository $repository): Response
    {
        return $this->renderTemplate(
            $prefix,
            'message/list.html.twig',
            ['messages' => $repository->findAllForAuthor($adherent)]
        );
    }

    /**
     * @Route("/creer", name="app_message_create", methods={"GET", "POST"})
     *
     * @param Adherent|UserInterface $adherent
     */
    public function createMessageAction(string $prefix, Request $request, UserInterface $adherent, ObjectManager $manager): Response
    {
        $form = $this
            ->createForm(AdherentMessageType::class)
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            $message = Factory::create($adherent, $form->getData());

            $manager->persist($message);
            $manager->flush();

            $this->addFlash('info', 'adherent_message.created_successfully');

            if ($form->get('next')->isClicked()) {
                return $this->redirectToRoute('app_message_filter', ['prefix' => $prefix, 'uuid' => $message->getUuid()->toString()]);
            }

            return $this->redirectToRoute('app_message_update', ['prefix' => $prefix, 'uuid' => $message->getUuid()]);
        }

        return $this->renderTemplate($prefix, 'message/create.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route("/{uuid}/modifier", name="app_message_update", methods={"GET", "POST"})
     * @Security("is_granted('IS_AUTHOR_OF', message)")
     */
    public function updateMessageAction(string $prefix, Request $request, AbstractAdherentMessage $message, ObjectManager $manager): Response
    {
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
                return $this->redirectToRoute('app_message_filter', ['prefix' => $prefix, 'uuid' => $message->getUuid()->toString()]);
            }

            return $this->redirectToRoute('app_message_update', ['prefix' => $prefix, 'uuid' => $message->getUuid()]);
        }

        return $this->renderTemplate($prefix, 'message/update.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route("/{uuid}/visualiser", name="app_message_preview", methods={"GET"})
     * @Security("message.isSynchronized() && is_granted('IS_AUTHOR_OF', message)")
     */
    public function previewMessageAction(string $prefix, AbstractAdherentMessage $message): Response
    {
        return $this->renderTemplate($prefix, 'message/preview.html.twig', ['message' => $message]);
    }

    /**
     * @Route("/{uuid}/supprimer", name="app_message_delete", methods={"GET"})
     */
    public function deleteAction(string $prefix, AbstractAdherentMessage $message, ObjectManager $manager): Response
    {
        $manager->remove($message);
        $manager->flush();

        $this->addFlash('info', 'adherent_message.deleted_successfully');

        return $this->redirectToRoute('app_message_list', ['prefix' => $prefix]);
    }

    /**
     * @Route("/{uuid}/content", name="app_message_content", methods={"GET"})
     * @Security("is_granted('IS_AUTHOR_OF', message)")
     */
    public function getMessageTemplateAction(AbstractAdherentMessage $message, Manager $manager): Response
    {
        return new Response($manager->getCampaignContent($message));
    }

    /**
     * @Route("/{uuid}/filtrer", name="app_message_filter", methods={"GET"})
     * @Security("is_granted('IS_AUTHOR_OF', message)")
     */
    public function filterMessageAction(string $prefix, Request $request, AbstractAdherentMessage $message, ObjectManager $manager): Response
    {
        // Reset Filter object
        if ($request->query->has('reset') && $message->getFilter()) {
            $message->setFilter(null);
            $manager->flush();

            return $this->redirectToRoute('app_message_filter', ['prefix' => $prefix, 'uuid' => $message->getUuid()->toString()]);
        }

        // Get Message filter or create a new object
        $filter = ($message->getFilter() ?? FilterFactory::create($message->getType()))->handleRequest($request);

        /** @var ReferentFilterDataObject $filter */
        if ($filter->hasToken()) {
            // Redirect if CSRF is invalid
            if (!$this->isCsrfTokenValid(ReferentController::TOKEN_ID, $filter->getToken())) {
                return $this->redirectToRoute('app_message_filter', ['prefix' => $prefix, 'uuid' => $message->getUuid()->toString()]);
            }

            $message->setFilter(clone $filter);
            $manager->flush();

            $this->addFlash('info', 'adherent_message.filter_updated');

            return $this->redirectToRoute('app_message_list', ['prefix' => $prefix, 'uuid' => $message->getUuid()->toString()]);
        }

        return $this->renderTemplate($prefix, 'message/filter.html.twig', [
            'message' => $message,
            'filter' => $filter,
        ]);
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
