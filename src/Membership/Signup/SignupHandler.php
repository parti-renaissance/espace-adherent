<?php

declare(strict_types=1);

namespace App\Membership\Signup;

use App\Entity\Adherent;
use App\Entity\AdherentSignupSource;
use App\Mailer\MailerService;
use App\Mailer\Message\Renaissance\SignupExcludedAdherentMessage;
use App\Membership\AdherentFactory;
use App\Membership\MembershipNotifier;
use App\Membership\Signup\Command\SendSignupConfirmationCommand;
use App\Repository\AdherentRepository;
use App\Repository\BannedAdherentRepository;
use App\Subscription\SubscriptionHandler;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Messenger\MessageBusInterface;

class SignupHandler
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ManagerRegistry $registry,
        private readonly AdherentRepository $adherentRepository,
        private readonly BannedAdherentRepository $bannedAdherentRepository,
        private readonly AdherentFactory $adherentFactory,
        private readonly SubscriptionHandler $subscriptionHandler,
        private readonly MembershipNotifier $membershipNotifier,
        private readonly MailerService $mailerService,
        private readonly MessageBusInterface $bus,
    ) {
    }

    public function handle(SignupCommand $command): void
    {
        $email = mb_strtolower($command->email);

        if ($this->bannedAdherentRepository->countForEmail($email) > 0) {
            $this->mailerService->sendMessage(SignupExcludedAdherentMessage::create($email));

            return;
        }

        $activeAdherent = $this->adherentRepository->findOneByEmailAndStatus(
            $email,
            [Adherent::PENDING, Adherent::ENABLED]
        );

        if (null !== $activeAdherent) {
            $this->logSourceIfMissing($this->entityManager, $activeAdherent, $command->source);
            $this->entityManager->flush();

            if (Adherent::PENDING === $activeAdherent->getStatus()) {
                $this->bus->dispatch(new SendSignupConfirmationCommand($activeAdherent));
            } else {
                $this->membershipNotifier->sendConnexionDetailsMessage($activeAdherent);
            }

            return;
        }

        $existingAdherent = $this->adherentRepository->findOneByEmail($email);

        if (null !== $existingAdherent) {
            return;
        }

        $adherent = $this->register($command, $email);

        $this->bus->dispatch(new SendSignupConfirmationCommand($adherent));
    }

    private function register(SignupCommand $command, string $email): Adherent
    {
        $adherent = $this->adherentFactory->createForSignup(
            $email,
            $command->gender,
            $command->firstName,
            $command->lastName,
            $command->phone,
            $command->address,
        );
        $this->entityManager->persist($adherent);

        $this->logSourceIfMissing($this->entityManager, $adherent, $command->source);

        try {
            $this->entityManager->flush();
        } catch (UniqueConstraintViolationException $exception) {
            $manager = $this->registry->resetManager();
            $adherent = $manager->getRepository(Adherent::class)->findOneBy(['emailAddress' => $email]);

            if (null === $adherent) {
                throw $exception;
            }

            $this->logSourceIfMissing($manager, $adherent, $command->source);

            try {
                $manager->flush();
            } catch (UniqueConstraintViolationException) {
            }

            return $adherent;
        }

        $this->subscriptionHandler->addDefaultTypesToAdherent($adherent, $command->emailOptIn, $command->smsOptIn);

        return $adherent;
    }

    private function logSourceIfMissing(ObjectManager $manager, Adherent $adherent, string $source): void
    {
        $alreadyLogged = $manager->getRepository(AdherentSignupSource::class)->findOneBy([
            'adherent' => $adherent,
            'source' => $source,
        ]);

        if (null === $alreadyLogged) {
            $manager->persist(new AdherentSignupSource($adherent, $source));
        }
    }
}
