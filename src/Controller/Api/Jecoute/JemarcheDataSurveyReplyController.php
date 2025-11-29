<?php

declare(strict_types=1);

namespace App\Controller\Api\Jecoute;

use App\Entity\Adherent;
use App\Entity\Jecoute\JemarcheDataSurvey;
use App\Jecoute\JemarcheDataSurveyEvent;
use App\Jecoute\SurveyEvents;
use App\Mailchimp\Synchronisation\Command\JemarcheDataSurveyCreateCommand;
use App\OAuth\Model\DeviceApiUser;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class JemarcheDataSurveyReplyController extends AbstractReplyController
{
    public const DESERIALIZE_GROUP = 'data_survey_write:include_survey';

    /** @var JemarcheDataSurvey */
    private $jemarcheDataSurvey;
    /** @var UserInterface */
    private $user;
    /** @var EventDispatcherInterface */
    private $dispatcher;
    /** @var MessageBusInterface */
    private $bus;

    public function __invoke(
        Request $request,
        JemarcheDataSurvey $jemarcheDataSurvey,
        EventDispatcherInterface $dispatcher,
        MessageBusInterface $bus,
    ): Response {
        $this->jemarcheDataSurvey = $jemarcheDataSurvey;
        $this->user = $this->getUser();
        $this->dispatcher = $dispatcher;
        $this->bus = $bus;

        return $this->handleRequest($request, $jemarcheDataSurvey);
    }

    protected function postHandleAction(): void
    {
        if ($this->user instanceof Adherent) {
            $this->jemarcheDataSurvey->getDataSurvey()->setAuthor($this->user);
            $this->jemarcheDataSurvey->getDataSurvey()->setAuthorPostalCode($this->user->getPostalCode());
        } elseif ($this->user instanceof DeviceApiUser) {
            $this->jemarcheDataSurvey->setDevice($this->user->getDevice());
            $this->jemarcheDataSurvey->getDataSurvey()->setAuthorPostalCode($this->user->getDevice()->getPostalCode());
        }
    }

    protected function dispatch(): void
    {
        $this->dispatcher->dispatch(new JemarcheDataSurveyEvent($this->jemarcheDataSurvey), SurveyEvents::JEMARCHE_DATA_SURVEY_ANSWERED);

        $email = $this->jemarcheDataSurvey->getEmailAddress();
        if ($email && $this->isValidEmail($email)) {
            $this->bus->dispatch(new JemarcheDataSurveyCreateCommand($email));
        }
    }

    protected function getCustomDeserializeGroups(): array
    {
        return [self::DESERIALIZE_GROUP];
    }

    private function isValidEmail(string $email): bool
    {
        $errors = $this->validator->validate($email, new Assert\Email());

        return 0 !== \count($errors);
    }
}
