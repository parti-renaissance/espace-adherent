<?php

namespace App\Jecoute;

use App\Entity\Adherent;
use App\Entity\Device;
use App\Entity\Jecoute\JemarcheDataSurvey;
use App\Mailchimp\Synchronisation\Command\JemarcheDataSurveyCreateCommand;
use Doctrine\ORM\EntityManagerInterface as ObjectManager;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class JemarcheDataSurveyAnswerHandler
{
    private $manager;
    private $dispatcher;
    private $bus;

    public function __construct(ObjectManager $manager, EventDispatcherInterface $dipatcher, MessageBusInterface $bus)
    {
        $this->manager = $manager;
        $this->dispatcher = $dipatcher;
        $this->bus = $bus;
    }

    public function handle(JemarcheDataSurvey $dataSurvey, Adherent $user): void
    {
        $dataSurvey->getDataSurvey()->setAuthor($user);

        $this->save($dataSurvey);
    }

    public function handleForDevice(JemarcheDataSurvey $dataSurvey, Device $device): void
    {
        $dataSurvey->setDevice($device);

        $this->save($dataSurvey);
    }

    private function save(JemarcheDataSurvey $dataSurvey): void
    {
        $this->manager->persist($dataSurvey);
        $this->manager->flush();

        $this->dispatcher->dispatch(new JemarcheDataSurveyEvent($dataSurvey), SurveyEvents::JEMARCHE_DATA_SURVEY_ANSWERED);
        if ($dataSurvey->getEmailAddress()) {
            $this->bus->dispatch(new JemarcheDataSurveyCreateCommand($dataSurvey->getEmailAddress()));
        }
    }
}
