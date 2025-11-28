<?php

declare(strict_types=1);

namespace App\Jecoute;

use App\Entity\Adherent;
use App\Entity\Device;
use App\Entity\Jecoute\JemarcheDataSurvey;
use App\Mailchimp\Synchronisation\Command\JemarcheDataSurveyCreateCommand;
use Doctrine\ORM\EntityManagerInterface as ObjectManager;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class JemarcheDataSurveyAnswerHandler
{
    private $manager;
    private $dispatcher;
    private $bus;
    private $validator;

    public function __construct(
        ObjectManager $manager,
        EventDispatcherInterface $dipatcher,
        MessageBusInterface $bus,
        ValidatorInterface $validator,
    ) {
        $this->manager = $manager;
        $this->dispatcher = $dipatcher;
        $this->bus = $bus;
        $this->validator = $validator;
    }

    public function handle(JemarcheDataSurvey $dataSurvey, Adherent $user): void
    {
        $dataSurvey->getDataSurvey()->setAuthor($user);
        $dataSurvey->getDataSurvey()->setAuthorPostalCode($user->getPostalCode());

        $this->save($dataSurvey);
    }

    public function handleForDevice(JemarcheDataSurvey $dataSurvey, Device $device): void
    {
        $dataSurvey->setDevice($device);
        $dataSurvey->getDataSurvey()->setAuthorPostalCode($device->getPostalCode());

        $this->save($dataSurvey);
    }

    private function save(JemarcheDataSurvey $dataSurvey): void
    {
        $this->manager->persist($dataSurvey);
        $this->manager->flush();

        $this->dispatcher->dispatch(new JemarcheDataSurveyEvent($dataSurvey), SurveyEvents::JEMARCHE_DATA_SURVEY_ANSWERED);

        $email = $dataSurvey->getEmailAddress();
        if ($email && $this->isValidEmail($email)) {
            $this->bus->dispatch(new JemarcheDataSurveyCreateCommand($email));
        }
    }

    private function isValidEmail(string $email): bool
    {
        $errors = $this->validator->validate($email, new Assert\Email());

        return 0 !== \count($errors);
    }
}
