<?php

namespace App\Controller\Renaissance;

use App\Mailer\MailerService;
use App\Mailer\Message\Renaissance\RenaissanceLoginLinkMessage;
use App\Repository\AdherentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\LoginLink\LoginLinkHandlerInterface;

#[Route(path: '/connexion/lien', name: 'app_user_get_login_link', methods: ['GET', 'POST'])]
class GetLoginLinkController extends AbstractController
{
    public function __invoke(
        Request $request,
        LoginLinkHandlerInterface $loginLinkHandler,
        AdherentRepository $adherentRepository,
        MailerService $transactionalMailer,
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
                $transactionalMailer->sendMessage(RenaissanceLoginLinkMessage::create($adherent, $loginLink->getUrl()));
            }

            $this->addFlash('success', 'Si l\'adresse que vous avez saisie est valide, un e-mail vous a été envoyé contenant un lien de connexion.');

            return $this->redirectToRoute('app_user_get_login_link');
        }

        return $this->render('security/renaissance_user_login_link.html.twig', ['form' => $form->createView()]);
    }
}
