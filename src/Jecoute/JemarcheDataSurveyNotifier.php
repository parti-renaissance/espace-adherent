<?php

namespace App\Jecoute;

use App\Entity\Jecoute\JemarcheDataSurvey;
use App\Mailer\MailerService;
use App\Mailer\Message\DataSurveyAnsweredMessage;
use App\Repository\AdherentRepository;
use App\Repository\Jecoute\JemarcheDataSurveyRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class JemarcheDataSurveyNotifier implements EventSubscriberInterface
{
    private $mailer;
    private $dataSurveyRepository;
    private $adherentRepository;

    public function __construct(
        MailerService $transactionalMailer,
        JemarcheDataSurveyRepository $dataSurveyRepository,
        AdherentRepository $adherentRepository
    ) {
        $this->mailer = $transactionalMailer;
        $this->dataSurveyRepository = $dataSurveyRepository;
        $this->adherentRepository = $adherentRepository;
    }

    public function onDataSurveyAnswered(JemarcheDataSurveyEvent $dataSurveyEvent): void
    {
        $dataSurvey = $dataSurveyEvent->getDataSurvey();

        if ($this->canNotify($dataSurvey)) {
            $this->mailer->sendMessage(DataSurveyAnsweredMessage::create($dataSurvey));
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            SurveyEvents::JEMARCHE_DATA_SURVEY_ANSWERED => 'onDataSurveyAnswered',
        ];
    }

    public function canNotify(JemarcheDataSurvey $dataSurvey): bool
    {
        if (!$email = $dataSurvey->getEmailAddress()) {
            return false;
        }

        if (null !== $this->adherentRepository->findOneByEmail($email)) {
            return false;
        }

        if ($this->dataSurveyRepository->countByEmailAnsweredForOneMonth(
            $dataSurvey->getEmailAddress(),
            $dataSurvey->getDataSurvey()->getPostedAt())
        ) {
            return false;
        }

        return true;
    }
}
