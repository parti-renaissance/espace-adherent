<?php

namespace App\Mailchimp\Synchronisation\Handler;

use App\Entity\Geo\Zone;
use App\Entity\Jecoute\DataSurvey;
use App\Geo\ZoneMatcher;
use App\Mailchimp\Manager;
use App\Mailchimp\Synchronisation\Command\DataSurveyCommandInterface;
use App\Repository\Geo\ZoneRepository;
use App\Repository\Jecoute\DataSurveyRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class DataSurveyCreateCommandHandler implements MessageHandlerInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    private $manager;
    private $entityManager;
    private $dataSurveyRepository;
    private $zoneRepository;
    private $zoneMatcher;

    public function __construct(
        Manager $manager,
        DataSurveyRepository $dataSurveyRepository,
        ZoneRepository $zoneRepository,
        ZoneMatcher $zoneMatcher,
        ObjectManager $entityManager
    ) {
        $this->manager = $manager;
        $this->entityManager = $entityManager;
        $this->dataSurveyRepository = $dataSurveyRepository;
        $this->zoneRepository = $zoneRepository;
        $this->zoneMatcher = $zoneMatcher;
        $this->logger = new NullLogger();
    }

    public function __invoke(DataSurveyCommandInterface $message): void
    {
        /** @var DataSurvey $dataSurvey */
        if (!$dataSurvey = $this->dataSurveyRepository->findLastAvailableToContactByEmail($email = $message->getEmail())) {
            $this->logger->warning(sprintf('DataSurvey contact available to contact with email "%s" not found, message skipped', $email));

            return;
        }

        $this->entityManager->refresh($dataSurvey);

        if (!$dataSurvey->getEmailAddress() || !$dataSurvey->getPostalCode()) {
            return;
        }

        $zoneCP = $this->zoneMatcher->matchPostalCode($dataSurvey->getPostalCode());

        if (!$zoneCP) {
            return;
        }

        $this->manager->editJecouteContact($dataSurvey, $zoneCP->getWithParents(Zone::CANDIDATE_TYPES));

        $this->entityManager->clear();
    }
}
