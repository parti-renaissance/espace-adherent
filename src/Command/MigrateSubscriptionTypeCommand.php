<?php

namespace AppBundle\Command;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\CitizenProjectMembership;
use AppBundle\Entity\District;
use AppBundle\Entity\SubscriptionType;
use AppBundle\Subscription\SubscriptionTypeEnum;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class MigrateSubscriptionTypeCommand extends Command
{
    protected static $defaultName = 'app:migrate:subscription-type';

    private $em;
    private $manager;
    private $adherentRepository;
    private $subscriptionTypeRepository;
    /**
     * @var SymfonyStyle
     */
    private $io;

    public function __construct(EntityManagerInterface $em, ObjectManager $manager)
    {
        parent::__construct();

        $this->em = $em;
        $this->manager = $manager;
        $this->adherentRepository = $this->em->getRepository(Adherent::class);
        $this->subscriptionTypeRepository = $this->em->getRepository(SubscriptionType::class);
    }

    protected function configure()
    {
        $this->setDescription('Migrate the subscription types from adherent.emails_subscriptions column to new table: subscription_type');
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->io->title('Starting email subscription type migration.');

        $progressBar = new ProgressBar($output, $this->getAdherentCount());
        $this->em->beginTransaction();

        $subscriptionTypes = [];
        foreach ($this->subscriptionTypeRepository->findAll() as $subscriptionType) {
            $subscriptionTypes[$subscriptionType->getCode()] = $subscriptionType;
        }

        try {
            foreach ($this->getIterator() as $index => $adherent) {
                /** @var Adherent $adherent */
                $adherent = $adherent[0];

                $adherentTypes = \array_filter($adherent->emailsSubscriptions, function (string $type) {
                    return \in_array($type, SubscriptionTypeEnum::DEFAULT_EMAIL_TYPES, true);
                });

                if ($adherent->comMobile) {
                    $adherentTypes[] = SubscriptionTypeEnum::MILITANT_ACTION_SMS;
                }

                if ($adherent->localHostEmailsSubscription) {
                    $adherentTypes[] = SubscriptionTypeEnum::LOCAL_HOST_EMAIL;
                }

                if ($adherent->getCitizenProjectMemberships()->getCitizenProjectFollowerMemberships()->count()) {
                    $adherentTypes[] = SubscriptionTypeEnum::CITIZEN_PROJECT_HOST_EMAIL;
                }

                if (array_diff($adherentTypes, $adherent->getEmailsSubscriptions())) {
                    foreach ($adherentTypes as $type) {
                        $adherent->addSubscriptionType($subscriptionTypes[$type]);
                    }
                }

                if (0 === $index % 1000) {
                    $this->manager->flush();
                    $this->manager->clear(Adherent::class);
                    $this->manager->clear(CitizenProjectMembership::class);
                    $this->manager->clear(District::class);
                    $progressBar->advance(500);
                }

                $this->manager->flush();
            }

            $this->em->commit();
        } catch (\Exception $exception) {
            $this->em->rollback();

            throw $exception;
        }

        $progressBar->finish();

        $this->io->newLine(2);
        $this->io->success('Email subscription type migration finished successfully!');
    }

    private function getAdherentCount(): int
    {
        return $this->em
            ->getRepository(Adherent::class)
            ->createQueryBuilder('adherent')
            ->select('COUNT(adherent)')
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    private function getIterator(): \Iterator
    {
        return $this->adherentRepository
            ->createQueryBuilder('a')
            ->getQuery()
            ->iterate()
        ;
    }
}
