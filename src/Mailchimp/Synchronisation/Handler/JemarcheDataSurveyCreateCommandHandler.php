<?php

namespace App\Mailchimp\Synchronisation\Handler;

use App\Entity\Geo\Zone;
use App\Entity\Jecoute\JemarcheDataSurvey;
use App\Geo\ZoneMatcher;
use App\Mailchimp\Manager;
use App\Mailchimp\Synchronisation\Command\JemarcheDataSurveyCommandInterface;
use App\Repository\Jecoute\JemarcheDataSurveyRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class JemarcheDataSurveyCreateCommandHandler implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    private $manager;
    private $entityManager;
    private $dataSurveyRepository;
    private $zoneMatcher;

    public function __construct(
        Manager $manager,
        JemarcheDataSurveyRepository $dataSurveyRepository,
        ZoneMatcher $zoneMatcher,
        EntityManagerInterface $entityManager,
    ) {
        $this->manager = $manager;
        $this->entityManager = $entityManager;
        $this->dataSurveyRepository = $dataSurveyRepository;
        $this->zoneMatcher = $zoneMatcher;
        $this->logger = new NullLogger();
    }

    public function __invoke(JemarcheDataSurveyCommandInterface $message): void
    {
        /** @var JemarcheDataSurvey $dataSurvey */
        if (!$dataSurvey = $this->dataSurveyRepository->findLastAvailableToContactByEmail($email = $message->getEmail())) {
            $this->logger->warning(\sprintf('DataSurvey contact available to contact with email "%s" not found, message skipped', $email));

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
