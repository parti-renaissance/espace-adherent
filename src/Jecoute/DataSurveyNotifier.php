<?php

namespace App\Jecoute;

use App\Entity\Jecoute\DataSurvey;
use App\Mailer\MailerService;
use App\Mailer\Message\DataSurveyAnsweredMessage;
use App\Repository\AdherentRepository;
use App\Repository\Jecoute\DataSurveyRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class DataSurveyNotifier implements EventSubscriberInterface
{
    private $mailer;
    private $dataSurveyRepository;
    private $adherentRepository;

    public function __construct(
        MailerService $mailer,
        DataSurveyRepository $dataSurveyRepository,
        AdherentRepository $adherentRepository
    ) {
        $this->mailer = $mailer;
        $this->dataSurveyRepository = $dataSurveyRepository;
        $this->adherentRepository = $adherentRepository;
    }

    public function onDataSurveyAnswered(DataSurveyEvent $dataSurveyEvent): void
    {
        $dataSurvey = $dataSurveyEvent->getDataSurvey();

        if ($this->canNotify($dataSurvey)) {
            $this->mailer->sendMessage(DataSurveyAnsweredMessage::create($dataSurvey));
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            SurveyEvents::DATA_SURVEY_ANSWERED => 'onDataSurveyAnswered',
        ];
    }

    public function canNotify(DataSurvey $dataSurvey): bool
    {
        if (!$email = $dataSurvey->getEmailAddress()) {
            return false;
        }

        if (null !== $this->adherentRepository->findOneByEmail($email)) {
            return false;
        }

        if ($this->dataSurveyRepository->countByEmailAnsweredForOneMonth(
            $dataSurvey->getEmailAddress(),
            $dataSurvey->getPostedAt())
        ) {
            return false;
        }

        return true;
    }
}
