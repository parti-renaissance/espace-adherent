<?php

namespace App\Command;

use App\Entity\Adherent;
use App\Entity\CitizenProjectMembership;
use App\Entity\SubscriptionType;
use App\Subscription\SubscriptionTypeEnum;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
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

        $militantActionSms = SubscriptionTypeEnum::MILITANT_ACTION_SMS;
        $localHostEmail = SubscriptionTypeEnum::LOCAL_HOST_EMAIL;
        $cpHostEmail = SubscriptionTypeEnum::CITIZEN_PROJECT_HOST_EMAIL;
        $follower = CitizenProjectMembership::CITIZEN_PROJECT_FOLLOWER;

        $this->em->beginTransaction();

        try {
            $sql = <<<SQL
INSERT INTO `adherent_subscription_type` (`adherent_id`, `subscription_type_id`)
SELECT `adherents`.`id`, `subscription_type`.`id`
FROM `adherents`
INNER JOIN `subscription_type` ON FIND_IN_SET(`subscription_type`.`code`, `adherents`.`emails_subscriptions`);

INSERT INTO `adherent_subscription_type` (`adherent_id`, `subscription_type_id`)
SELECT `adherents`.`id`, `subscription_type`.`id`
FROM `adherents`
INNER JOIN `subscription_type` ON `subscription_type`.`code` = '{$militantActionSms}'
WHERE `adherents`.`com_mobile` = 1;

INSERT INTO `adherent_subscription_type` (`adherent_id`, `subscription_type_id`)
SELECT `adherents`.`id`, `subscription_type`.`id`
FROM `adherents`
INNER JOIN `subscription_type` ON `subscription_type`.`code` = '{$localHostEmail}'
WHERE `adherents`.`local_host_emails_subscription` = 1;

INSERT INTO `adherent_subscription_type` (`adherent_id`, `subscription_type_id`)
SELECT DISTINCT(`adherents`.`id`), `subscription_type`.`id`
FROM `adherents`
INNER JOIN `citizen_project_memberships` ON `citizen_project_memberships`.`adherent_id` = `adherents`.`id`
INNER JOIN `subscription_type` ON `subscription_type`.`code` = '{$cpHostEmail}'
WHERE `citizen_project_memberships`.`privilege` = '{$follower}';
SQL;

            $this->em->getConnection()->exec($sql);
            $this->em->commit();
        } catch (\Exception $exception) {
            $this->em->rollback();

            throw $exception;
        }

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
