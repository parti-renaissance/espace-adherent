<?php

namespace App\Controller\Renaissance;

use App\Mailer\MailerService;
use App\Mailer\Message\Renaissance\RenaissanceMagicLinkMessage;
use App\Repository\AdherentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\LoginLink\LoginLinkHandlerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class MagicLinkController extends AbstractController
{
    #[Route(path: '/demander-un-lien-magique', name: 'app_user_get_magic_link', methods: ['GET', 'POST'])]
    public function getMagicLinkAction(
        Request $request,
        LoginLinkHandlerInterface $loginLinkHandler,
        AdherentRepository $adherentRepository,
        MailerService $transactionalMailer,
        TranslatorInterface $translator,
    ): Response {
        if ($this->getUser()) {
            $this->addFlash('info', 'Vous êtes déjà connecté(e)');

            return $this->redirectToRoute('app_renaissance_adherent_space');
        }

        $form = $this
            ->createForm(EmailType::class)
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            $email = $form->getData();

            if ($adherent = $adherentRepository->findOneActiveByEmail($email)) {
                $loginLink = $loginLinkHandler->createLoginLink($adherent);
                $transactionalMailer->sendMessage(RenaissanceMagicLinkMessage::create($adherent, $loginLink->getUrl()));
            }

            $this->addFlash('info', $translator->trans('adherent.get_magic_link.email_sent', ['%email%' => $email]));

            return $this->redirectToRoute('app_user_get_magic_link');
        }

        return $this->render('security/renaissance_user_magic_link.html.twig', ['form' => $form->createView()]);
    }

    #[Route(path: '/connexion-avec-un-lien-magique', name: 'app_user_connect_with_magic_link', methods: ['GET', 'POST'])]
    public function connectViaMagicLinkAction(Request $request): Response
    {
        // get the login link query parameters
        $expires = $request->query->get('expires');
        $username = $request->query->get('user');
        $hash = $request->query->get('hash');

        return $this->render('security/renaissance_connect_magic_link.html.twig', [
            'expires' => $expires,
            'user' => $username,
            'hash' => $hash,
        ]);
    }
}
