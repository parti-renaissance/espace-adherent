<?php

declare(strict_types=1);

namespace App\Controller\Renaissance;

use App\AppCodeEnum;
use App\Entity\Adherent;
use App\Entity\Administrator;
use App\Mailer\MailerService;
use App\Mailer\Message\Renaissance\RenaissanceMagicLinkMessage;
use App\Repository\AdherentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
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
    ): Response {
        if ($user = $this->getUser()) {
            if ($user instanceof Administrator) {
                return $this->redirectToRoute('admin_app_adherent_list');
            }

            return $this->redirectToRoute('vox_app_redirect');
        }

        $form = $this
            ->createForm(EmailType::class, null, ['constraints' => new NotBlank()])
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            $email = $form->getData();

            if ($adherent = $adherentRepository->findOneByEmailAndStatus($email, [Adherent::PENDING, Adherent::ENABLED])) {
                $loginLink = $loginLinkHandler->createLoginLink($adherent, $request, appCode: AppCodeEnum::VOX);

                $transactionalMailer->sendMessage(RenaissanceMagicLinkMessage::create($adherent, $loginLink->getUrl()));
            }

            $this->addFlash('info', $translator->trans('adherent.get_magic_link.email_sent', ['%email%' => $email]));

            return $this->redirectToRoute('app_user_get_magic_link');
        }

        return $this->render('security/renaissance_user_magic_link.html.twig', ['form' => $form->createView()]);
    }

    public function connectViaMagicLinkAction(Request $request, Security $security): Response
    {
        $currentUser = $this->getUser();

        if ($currentUser) {
            if ($currentUser->getUserIdentifier() !== $request->query->get('user')) {
                $security->logout(false);

                return $this->redirect($request->getRequestUri());
            }

            if ($currentUser instanceof Administrator) {
                return $this->redirectToRoute('admin_app_adherent_list');
            }

            if ($targetPath = $request->query->get('_target_path')) {
                $redirectUri = parse_url($targetPath, \PHP_URL_PATH);

                if ($queryParams = parse_url($targetPath, \PHP_URL_QUERY)) {
                    $redirectUri .= '?'.$queryParams;
                }

                return $this->redirect($redirectUri ?: '/');
            }

            return $this->redirectToRoute('vox_app_redirect');
        }

        return $this->render('security/renaissance_connect_magic_link.html.twig', [
            'expires' => $request->query->get('expires'),
            'user' => $request->query->get('user'),
            'hash' => $request->query->get('hash'),
            'target_path' => $request->query->get('_target_path'),
            'failure_path' => $request->query->get('_failure_path'),
        ]);
    }
}
