<?php

declare(strict_types=1);

namespace App\Membership\Signup;

use App\Entity\Adherent;
use App\Entity\AdherentSignupSource;
use App\Membership\AdherentFactory;
use App\Repository\AdherentRepository;
use App\Subscription\SubscriptionHandler;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;

class SignupHandler
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ManagerRegistry $registry,
        private readonly AdherentRepository $adherentRepository,
        private readonly AdherentFactory $adherentFactory,
        private readonly SubscriptionHandler $subscriptionHandler,
    ) {
    }

    public function register(SignupCommand $command): Adherent
    {
        $email = mb_strtolower($command->email);
        $adherent = $this->adherentRepository->findOneByEmail($email);
        $created = null === $adherent;

        if ($created) {
            $adherent = $this->adherentFactory->createForSignup(
                $email,
                $command->gender,
                $command->firstName,
                $command->lastName,
                $command->phone,
                $command->address,
            );
            $this->entityManager->persist($adherent);
        }

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

        if ($created) {
            $this->subscriptionHandler->addDefaultTypesToAdherent($adherent, $command->emailOptIn, $command->smsOptIn);
        }

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
