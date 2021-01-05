<?php

namespace App\Jecoute;

use App\Entity\Adherent;
use App\Entity\Device;
use App\Entity\Jecoute\DataSurvey;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class DataSurveyAnswerHandler
{
    private $manager;
    private $dispatcher;

    public function __construct(ObjectManager $manager, EventDispatcherInterface $dipatcher)
    {
        $this->manager = $manager;
        $this->dispatcher = $dipatcher;
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
    }
}
