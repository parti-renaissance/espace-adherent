<?php

namespace App\Controller\Api;

use App\Adherent\AdherentInstances;
use App\Adherent\Unregistration\UnregistrationCommand;
use App\AdherentProfile\AdherentProfile;
use App\AdherentProfile\AdherentProfileConfiguration;
use App\AdherentProfile\AdherentProfileHandler;
use App\AdherentProfile\PasswordChangeRequest;
use App\Committee\CommitteeMembershipManager;
use App\Committee\CommitteeMembershipTriggerEnum;
use App\Donation\DonationManager;
use App\Donation\Paybox\PayboxPaymentUnsubscription;
use App\Entity\Adherent;
use App\Entity\Committee;
use App\Entity\Geo\Zone;
use App\Entity\TaxReceipt;
use App\Exception\PayboxPaymentUnsubscriptionException;
use App\Membership\AdherentChangePasswordHandler;
use App\Membership\Event\UserEvent;
use App\Membership\MembershipRequestHandler;
use App\Membership\MembershipSourceEnum;
use App\Membership\UserEvents;
use App\Normalizer\ImageExposeNormalizer;
use App\OAuth\TokenRevocationAuthority;
use App\Repository\CommitteeRepository;
use App\Repository\DonationRepository;
use App\Repository\TaxReceiptRepository;
use App\Repository\VotingPlatform\VoterRepository;
use App\Subscription\SubscriptionHandler;
use App\Utils\HttpUtils;
use Doctrine\ORM\EntityManagerInterface;
use League\Flysystem\FilesystemOperator;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

#[Route(path: '/v3/profile', name: 'app_api_user_profile')]
class ProfileController extends AbstractController
{
    private const READ_PROFILE_SERIALIZATION_GROUPS = [
        'profile_read',
        ImageExposeNormalizer::NORMALIZATION_GROUP,
    ];

    private const WRITE_PROFILE_SERIALIZATION_GROUPS = [
        'profile_write',
    ];

    private const WRITE_UNCERTIFIED_PROFILE_SERIALIZATION_GROUPS = 'uncertified_profile_write';

    #[IsGranted('ROLE_OAUTH_SCOPE_READ:PROFILE')]
    #[Route(path: '/me', name: '_show', methods: ['GET'])]
    public function show(SerializerInterface $serializer): JsonResponse
    {
        /** @var Adherent $user */
        $user = $this->getUser();

        return JsonResponse::fromJsonString(
            $serializer->serialize($user, 'json', [
                AbstractNormalizer::GROUPS => self::READ_PROFILE_SERIALIZATION_GROUPS,
            ])
        );
    }

    #[IsGranted('ROLE_OAUTH_SCOPE_READ:PROFILE')]
    #[Route(path: '/me/donations', name: '_donations_show', methods: ['GET'])]
    public function showDonations(SerializerInterface $serializer, DonationManager $donationManager): JsonResponse
    {
        /** @var Adherent $user */
        $user = $this->getUser();

        return JsonResponse::fromJsonString(
            $serializer->serialize(
                $donationManager->getHistory($user),
                'json',
                [AbstractNormalizer::GROUPS => ['donation_read']]
            )
        );
    }

    #[IsGranted('ROLE_OAUTH_SCOPE_READ:PROFILE')]
    #[Route(path: '/me/tax_receipts', name: '_tax_receipts', methods: ['GET'])]
    public function getTaxReceipts(TaxReceiptRepository $repository): JsonResponse
    {
        return $this->json($repository->findAllByAdherent($this->getUser()), context: ['groups' => ['tax_receipt:list']]);
    }

    #[IsGranted('ROLE_OAUTH_SCOPE_READ:PROFILE')]
    #[Route(path: '/me/tax_receipts/{uuid}/file', name: '_tax_receipts_download', methods: ['GET'])]
    public function downloadTaxReceiptFile(TaxReceipt $receipt, FilesystemOperator $defaultStorage): Response
    {
        $user = $this->getUser();

        if ($receipt->donator->getAdherent() !== $user) {
            return $this->json(['message' => 'Tax receipt not found'], Response::HTTP_NOT_FOUND);
        }

        return HttpUtils::createResponse($defaultStorage, $receipt->getFilePath(), $receipt->label);
    }

    #[IsGranted('ROLE_OAUTH_SCOPE_READ:PROFILE')]
    #[Route(path: '/me/donations/cancel', name: '_donations_cancel', methods: ['POST'])]
    public function cancelDonations(
        EntityManagerInterface $entityManager,
        DonationRepository $donationRepository,
        PayboxPaymentUnsubscription $payboxPaymentUnsubscription,
        LoggerInterface $logger,
    ): JsonResponse {
        /** @var Adherent $user */
        $user = $this->getUser();

        $donations = $donationRepository->findAllSubscribedDonationByEmail($user->getEmailAddress());

        foreach ($donations as $donation) {
            try {
                $payboxPaymentUnsubscription->unsubscribe($donation);
                $entityManager->flush();
                $payboxPaymentUnsubscription->sendConfirmationMessage($donation, $user);

                $logger->info(\sprintf('Subscription donation id(%d) from user email %s have been cancel successfully.', $donation->getId(), $user->getEmailAddress()));
            } catch (PayboxPaymentUnsubscriptionException $e) {
                $logger->error(\sprintf('Subscription donation id(%d) from user email %s have an error.', $donation->getId(), $user->getEmailAddress()), ['exception' => $e]);
            }
        }

        return new JsonResponse('OK');
    }

    #[IsGranted(new Expression("is_granted('ROLE_OAUTH_SCOPE_WRITE:PROFILE') and user == subject"), 'adherent')]
    #[Route(path: '/{uuid}', name: '_update', methods: ['PUT'])]
    public function update(
        Request $request,
        SerializerInterface $serializer,
        ValidatorInterface $validator,
        AdherentProfileHandler $handler,
        Adherent $adherent,
    ): JsonResponse {
        $json = $request->getContent();

        $adherentProfile = AdherentProfile::createFromAdherent($adherent);

        $groups = self::WRITE_PROFILE_SERIALIZATION_GROUPS;
        if (!$adherent->isCertified()) {
            $groups[] = self::WRITE_UNCERTIFIED_PROFILE_SERIALIZATION_GROUPS;
        } elseif (!$adherent->getBirthdate()) {
            $groups[] = 'empty_profile_data';
        }

        $serializer->deserialize($json, AdherentProfile::class, 'json', context: [
            AbstractNormalizer::OBJECT_TO_POPULATE => $adherentProfile,
            AbstractObjectNormalizer::DEEP_OBJECT_TO_POPULATE => true,
            AbstractNormalizer::GROUPS => $groups,
        ]);

        $violations = $validator->validate($adherentProfile, null, ['Default', 'api_put_validation']);

        if (0 === $violations->count()) {
            $handler->update($adherent, $adherentProfile);

            return new JsonResponse('OK');
        }

        $errors = $serializer->serialize($violations, 'jsonproblem');

        return JsonResponse::fromJsonString($errors, Response::HTTP_BAD_REQUEST);
    }

    #[IsGranted('ROLE_OAUTH_SCOPE_WRITE:PROFILE')]
    #[Route(path: '/me/password-change', name: '_password_change', methods: ['POST'])]
    public function changePassword(
        Request $request,
        SerializerInterface $serializer,
        ValidatorInterface $validator,
        AdherentChangePasswordHandler $changePasswordHandler,
    ): JsonResponse {
        $json = $request->getContent();

        /** @var Adherent $adherent */
        $adherent = $this->getUser();
        $passwordChangeRequest = new PasswordChangeRequest();

        $serializer->deserialize($json, PasswordChangeRequest::class, 'json', [
            AbstractNormalizer::OBJECT_TO_POPULATE => $passwordChangeRequest,
            AbstractObjectNormalizer::DEEP_OBJECT_TO_POPULATE => true,
        ]);

        $violations = $validator->validate($passwordChangeRequest);

        if (0 === $violations->count()) {
            $changePasswordHandler->changePassword($adherent, $passwordChangeRequest->newPassword);

            return new JsonResponse('OK');
        }

        $errors = $serializer->serialize($violations, 'jsonproblem');

        return JsonResponse::fromJsonString($errors, Response::HTTP_BAD_REQUEST);
    }

    #[IsGranted('ROLE_OAUTH_SCOPE_READ:PROFILE')]
    #[Route(path: '/committees', methods: ['GET'])]
    public function showCommitteesOfMyZone(UserInterface $adherent, CommitteeRepository $committeeRepository): Response
    {
        /** @var Adherent $adherent */
        return $this->json($committeeRepository->findInAdherentZone($adherent), context: ['groups' => ['committee:list']]);
    }

    #[IsGranted('ROLE_OAUTH_SCOPE_READ:PROFILE')]
    #[Route(path: '/instances', methods: ['GET'])]
    public function myInstances(UserInterface $adherent, AdherentInstances $adherentInstances): Response
    {
        /** @var Adherent $adherent */
        $instances = array_filter($adherentInstances->generate($adherent));

        // Flatten array of Agoras with other instances
        if (\array_key_exists('agoras', $instances) && is_iterable($instances['agoras'])) {
            $instances = array_merge($instances, $instances['agoras']);
            unset($instances['agoras']);
        }

        return $this->json(array_values($instances));
    }

    #[IsGranted(new Expression('is_granted("ROLE_OAUTH_SCOPE_WRITE:PROFILE") and user.isRenaissanceAdherent()'))]
    #[Route(path: '/committees/{uuid}/join', methods: ['PUT'])]
    public function saveMyNewCommittee(
        Committee $committee,
        UserInterface $adherent,
        CommitteeMembershipManager $committeeMembershipManager,
        VoterRepository $voterRepository,
    ): Response {
        /** @var Adherent $adherent */
        if (
            !array_intersect(
                $adherent->getParentZonesOfType($adherent->isForeignResident() ? Zone::CUSTOM : Zone::DEPARTMENT),
                $committee->getParentZonesOfType($adherent->isForeignResident() ? Zone::CUSTOM : Zone::DEPARTMENT)
            )
        ) {
            return $this->json([
                'message' => 'Le comité choisi n\'est pas dans l\'assemblée départementale',
            ], Response::HTTP_BAD_REQUEST);
        }

        $currentCommittee = $adherent->getCommitteeMembership()?->getCommittee();

        if (
            $currentCommittee
            && $voterRepository->isInVoterListForCommitteeElection(
                $adherent,
                $currentCommittee,
                new \DateTime('-3 months')
            )
        ) {
            return $this->json([
                'message' => 'Vous avez participé à une élection interne il y a moins de 3 mois dans votre comité. Il ne vous est pas possible d\'en changer.',
            ], Response::HTTP_BAD_REQUEST);
        }

        $committeeMembershipManager->followCommittee(
            $adherent,
            $committee,
            CommitteeMembershipTriggerEnum::MANUAL
        );

        return $this->json('OK');
    }

    #[IsGranted('ROLE_OAUTH_SCOPE_WRITE:PROFILE')]
    #[Route(path: '/configuration', name: '_configuration', methods: ['GET'])]
    public function configuration(AdherentProfileConfiguration $adherentProfileConfiguration): JsonResponse
    {
        return new JsonResponse($adherentProfileConfiguration->build());
    }

    #[IsGranted('UNREGISTER', subject: 'user')]
    #[Route(path: '/unregister', name: '_unregister', methods: ['POST'])]
    public function terminateMembershipAction(
        Request $request,
        SerializerInterface $serializer,
        ValidatorInterface $validator,
        MembershipRequestHandler $handler,
        UserInterface $user,
        TokenRevocationAuthority $tokenRevocationAuthority,
    ): Response {
        /** @var UnregistrationCommand $unregistrationCommand */
        $unregistrationCommand = $serializer->deserialize($request->getContent() ?: '{}', UnregistrationCommand::class, 'json', [
            AbstractNormalizer::GROUPS => ['unregister'],
        ]);

        /** @var Adherent $user */
        $validationGroups = [$user->isRenaissanceAdherent() ? 'unregister_adherent' : 'unregister_user'];

        if ($user->getAuthAppVersion() >= 5180000) {
            $validationGroups[] = 'unregister';
        }

        $violations = $validator->validate($unregistrationCommand, null, $validationGroups);

        if (0 < $violations->count()) {
            $errors = $serializer->serialize($violations, 'jsonproblem');

            return JsonResponse::fromJsonString($errors, Response::HTTP_BAD_REQUEST);
        }

        $handler->terminateMembership(
            $user,
            $unregistrationCommand,
            $user instanceof Adherent && MembershipSourceEnum::BESOIN_D_EUROPE === $user->getSource(),
        );

        $tokenRevocationAuthority->revokeUserTokens($user);

        return $this->json('OK');
    }

    #[Route('/unsubscribe', methods: ['POST'])]
    public function unsubscribe(UserInterface $adherent, SubscriptionHandler $subscriptionHandler, EntityManagerInterface $entityManager, EventDispatcherInterface $dispatcher): Response
    {
        /** @var Adherent $adherent */
        $adherent->markAsUnsubscribe();
        $subscriptionHandler->handleUpdateSubscription($adherent, []);

        $entityManager->flush();

        $dispatcher->dispatch(new UserEvent($adherent), UserEvents::USER_UPDATED);

        return $this->json(['message' => 'OK', 'status' => 'success'], Response::HTTP_OK);
    }
}
