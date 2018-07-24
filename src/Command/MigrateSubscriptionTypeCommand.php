<?php

namespace AppBundle\Command;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\CitizenProjectMembership;
use AppBundle\Entity\District;
use AppBundle\Repository\AdherentRepository;
use AppBundle\Repository\SubscriptionTypeRepository;
use AppBundle\Subscription\SubscriptionTypeEnum;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MigrateSubscriptionTypeCommand extends Command
{
    protected static $defaultName = 'app:migrate:subscription-type';

    private $manager;
    private $adherentRepository;
    private $subscriptionTypeRepository;

    public function __construct(ObjectManager $manager, AdherentRepository $adherentRepository, SubscriptionTypeRepository $subscriptionTypeRepository)
    {
        parent::__construct();

        $this->manager = $manager;
        $this->adherentRepository = $adherentRepository;
        $this->subscriptionTypeRepository = $subscriptionTypeRepository;
    }

    protected function configure()
    {
        $this->setDescription('Migrate the subscription types from adherent.emails_subscriptions column to new table: subscription_type');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
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
                $adherent->setSubscriptionTypes($this->subscriptionTypeRepository->findByCodes($adherentTypes));
            }

            if (0 === $index % 500) {
                $this->manager->flush();
                $this->manager->clear(Adherent::class);
                $this->manager->clear(CitizenProjectMembership::class);
                $this->manager->clear(District::class);
            }

            $this->manager->flush();
        }
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
