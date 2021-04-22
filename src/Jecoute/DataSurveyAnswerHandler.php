<?php

namespace App\Jecoute;

use App\Entity\Adherent;
use App\Entity\Device;
use App\Entity\Jecoute\DataSurvey;
use App\Mailchimp\Synchronisation\Command\DataSurveyCreateCommand;
use Doctrine\ORM\EntityManagerInterface as ObjectManager;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class DataSurveyAnswerHandler
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

    public function handle(DataSurvey $dataSurvey, Adherent $user): void
    {
        $dataSurvey->setAuthor($user);

        $this->save($dataSurvey);
    }

    public function handleForDevice(DataSurvey $dataSurvey, Device $device): void
    {
        $dataSurvey->setDevice($device);

        $this->save($dataSurvey);
    }

    private function save(DataSurvey $dataSurvey): void
    {
        $this->manager->persist($dataSurvey);
        $this->manager->flush();

        $this->dispatcher->dispatch(new DataSurveyEvent($dataSurvey), SurveyEvents::DATA_SURVEY_ANSWERED);
        if ($dataSurvey->getEmailAddress()) {
            $this->bus->dispatch(new DataSurveyCreateCommand($dataSurvey->getEmailAddress()));
        }
    }
}
