<?php

namespace App\Controller\Renaissance;

use App\AppCodeEnum;
use App\Mailer\MailerService;
use App\Mailer\Message\BesoinDEurope\BesoinDEuropeMagicLinkMessage;
use App\Mailer\Message\Renaissance\RenaissanceMagicLinkMessage;
use App\OAuth\App\AuthAppUrlManager;
use App\Repository\AdherentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\LoginLink\LoginLinkHandlerInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Contracts\Translation\TranslatorInterface;

class MagicLinkController extends AbstractController
{
    public const ROUTE_NAME = 'app_user_connect_with_magic_link';

    public function getMagicLinkAction(
        Request $request,
        LoginLinkHandlerInterface $loginLinkHandler,
        AdherentRepository $adherentRepository,
        MailerService $transactionalMailer,
        TranslatorInterface $translator,
        AuthAppUrlManager $appUrlManager,
    ): Response {
        $appUrlGenerator = $appUrlManager->getUrlGenerator($appCode = $appUrlManager->getAppCodeFromRequest($request) ?? AppCodeEnum::RENAISSANCE);

        if ($user = $this->getUser()) {
            return $this->redirect($appUrlGenerator->generateForLoginSuccess($user));
        }

        $form = $this
            ->createForm(EmailType::class, null, ['constraints' => new NotBlank()])
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            $email = $form->getData();

            if ($adherent = $adherentRepository->findOneActiveByEmail($email)) {
                $loginLink = $loginLinkHandler->createLoginLink($adherent, null, $appCode);

                if (AppCodeEnum::isBesoinDEuropeApp($appCode)) {
                    $transactionalMailer->sendMessage(BesoinDEuropeMagicLinkMessage::create($adherent, $loginLink->getUrl()));
                } else {
                    $transactionalMailer->sendMessage(RenaissanceMagicLinkMessage::create($adherent, $loginLink->getUrl()));
                }
            }

            $this->addFlash('info', $translator->trans('adherent.get_magic_link.email_sent', ['%email%' => $email]));

            return $this->redirectToRoute('app_user_get_magic_link', ['app_domain' => $appUrlGenerator->getAppHost()]);
        }

        return $this->render(sprintf('security/%s_user_magic_link.html.twig', $appUrlGenerator::getAppCode()), ['form' => $form->createView()]);
    }

    public function connectViaMagicLinkAction(Request $request, AuthAppUrlManager $appUrlManager): Response
    {
        $appUrlGenerator = $appUrlManager->getUrlGenerator($appUrlManager->getAppCodeFromRequest($request) ?? AppCodeEnum::RENAISSANCE);

        return $this->render(sprintf('security/%s_connect_magic_link.html.twig', $appUrlGenerator::getAppCode()), [
            'expires' => $request->query->get('expires'),
            'user' => $request->query->get('user'),
            'hash' => $request->query->get('hash'),
            'target_path' => $request->query->get('_target_path'),
            'failure_path' => $request->query->get('_failure_path'),
        ]);
    }
}
