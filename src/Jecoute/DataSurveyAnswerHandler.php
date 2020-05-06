<?php

namespace App\Jecoute;

use App\Entity\Adherent;
use App\Entity\Jecoute\DataSurvey;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

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

        $this->manager->persist($dataSurvey);
        $this->manager->flush();

        $this->dispatcher->dispatch(SurveyEvents::DATA_SURVEY_ANSWERED, new DataSurveyEvent($dataSurvey));
    }
}
