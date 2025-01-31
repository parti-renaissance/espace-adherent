<?php

namespace App\Controller\EnMarche;

use App\Adherent\Unregistration\UnregistrationCommand;
use App\AdherentCharter\AdherentCharterFactory;
use App\AdherentCharter\AdherentCharterTypeEnum;
use App\AdherentProfile\AdherentProfile;
use App\AdherentProfile\AdherentProfileHandler;
use App\AppCodeEnum;
use App\Entity\Adherent;
use App\Entity\Unregistration;
use App\Form\AdherentChangePasswordType;
use App\Form\AdherentProfileType;
use App\Form\UnregistrationType;
use App\Membership\AdherentChangePasswordHandler;
use App\Membership\MembershipRequestHandler;
use App\OAuth\App\AuthAppUrlManager;
use Doctrine\ORM\EntityManagerInterface as ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security as CoreSecurity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route(path: '/parametres/mon-compte')]
class UserController extends AbstractController
{
    private const UNREGISTER_TOKEN = 'unregister_token';

    #[Route(name: 'app_user_edit', requirements: ['app_domain' => '%app_host%'], defaults: ['app_domain' => '%app_host%'], methods: ['GET', 'POST'])]
    public function profileAction(Request $request, AdherentProfileHandler $handler): Response
    {
        /** @var Adherent $adherent */
        $adherent = $this->getUser();

        if ($adherent->isRenaissanceUser()) {
            return $this->render('adherent/renaissance_profile.html.twig');
        }

        $adherentProfile = AdherentProfile::createFromAdherent($adherent);

        $form = $this
            ->createForm(AdherentProfileType::class, $adherentProfile, [
                'disabled_form' => $adherent->isCertified(),
            ])
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            $handler->update($adherent, $adherentProfile);
            $this->addFlash('info', 'adherent.update_profile.success');

            return $this->redirectToRoute('app_user_edit');
        }

        return $this->render('adherent/profile.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * This action enables an adherent to change his/her current password.
     */
    #[Route(path: '/changer-mot-de-passe', name: 'app_user_change_password', methods: ['GET', 'POST'])]
    public function changePasswordAction(
        Request $request,
        AdherentChangePasswordHandler $handler,
        AuthAppUrlManager $appUrlManager,
        string $app_domain,
    ): Response {
        $appCode = $appUrlManager->getAppCodeFromRequest($request);
        $isRenaissanceApp = AppCodeEnum::isRenaissanceApp($appCode);

        /** @var Adherent $adherent */
        $adherent = $this->getUser();

        if (!$isRenaissanceApp && $adherent->isRenaissanceUser()) {
            return $this->render('adherent/renaissance_profile.html.twig');
        }

        $form = $this
            ->createForm(AdherentChangePasswordType::class)
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            $handler->changePassword($adherent, $form->get('password')->getData());
            $this->addFlash('info', 'adherent.update_password.success');

            return $this->redirectToRoute('app_user_change_password', ['app_domain' => $app_domain]);
        }

        return $this->render(
            $isRenaissanceApp
                ? 'renaissance/adherent/change_password/form.html.twig'
                : 'adherent/change_password.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }

    #[IsGranted('UNREGISTER', subject: 'user')]
    #[Route(path: '/desadherer', name: 'app_user_terminate_membership', methods: ['GET', 'POST'])]
    public function terminateMembershipAction(
        Request $request,
        MembershipRequestHandler $handler,
        AuthAppUrlManager $appUrlManager,
        UserInterface $user,
        CoreSecurity $security,
    ): Response {
        $appCode = $appUrlManager->getAppCodeFromRequest($request);
        $isRenaissanceApp = AppCodeEnum::isRenaissanceApp($appCode);

        if (!$isRenaissanceApp && $user->isRenaissanceUser()) {
            return $this->render('adherent/renaissance_profile.html.twig');
        }

        $unregistrationCommand = new UnregistrationCommand();
        /** @var Adherent $user */
        $viewFolder = $user->isRenaissanceSympathizer() ? 'user' : 'adherent';
        $reasons = $user->isRenaissanceSympathizer() ? Unregistration::REASONS_LIST_USER : Unregistration::REASONS_LIST_ADHERENT;

        $form = $this->createForm(UnregistrationType::class, $unregistrationCommand, [
            'csrf_token_id' => self::UNREGISTER_TOKEN,
            'reasons' => array_combine($reasons, $reasons),
            'is_renaissance' => $isRenaissanceApp,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $handler->terminateMembership($user, $unregistrationCommand, $user->isRenaissanceAdherent());
            $security->logout(false);

            return $this->render(
                $isRenaissanceApp
                    ? 'renaissance/adherent/terminate_membership/success.html.twig'
                    : \sprintf('%s/terminate_membership.html.twig', $viewFolder),
                [
                    'unregistered' => true,
                    'form' => $form->createView(),
                ]
            );
        }

        return $this->render(
            $isRenaissanceApp
                ? 'renaissance/adherent/terminate_membership/form.html.twig'
                : \sprintf('%s/terminate_membership.html.twig', $viewFolder),
            [
                'unregistered' => false,
                'form' => $form->createView(),
            ]
        );
    }

    #[Route(path: '/chart', name: 'app_user_set_accept_chart', condition: 'request.isXmlHttpRequest()', methods: ['PUT'])]
    public function chartAcceptationAction(Request $request, ObjectManager $manager): JsonResponse
    {
        /** @var Adherent $adherent */
        $adherent = $this->getUser();

        $charterType = $request->request->all()['charterType'] ?? null;

        if (!AdherentCharterTypeEnum::isValid($charterType)) {
            throw new BadRequestHttpException('Invalid charter type');
        }

        /** @var Adherent $adherent */
        if (!$adherent->getCharters()->hasCharterAcceptedForType($charterType)) {
            $adherent->addCharter(AdherentCharterFactory::create($charterType));
            $manager->flush();
        }

        return new JsonResponse('OK', Response::HTTP_OK);
    }
}
