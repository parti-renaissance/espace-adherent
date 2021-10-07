<?php

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
        UserInterface $user,
        EventDispatcherInterface $dispatcher,
        MessageBusInterface $bus
    ): Response {
        $this->jemarcheDataSurvey = $jemarcheDataSurvey;
        $this->user = $user;
        $this->dispatcher = $dispatcher;
        $this->bus = $bus;

        return $this->handleRequest($request, $jemarcheDataSurvey);
    }

    protected function postHandleAction(): void
    {
        if ($this->user instanceof Adherent) {
            $this->jemarcheDataSurvey->getDataSurvey()->setAuthor($this->user);
        } elseif ($this->user instanceof DeviceApiUser) {
            $this->jemarcheDataSurvey->setDevice($this->user->getDevice());
        }
    }

    protected function dispatch(): void
    {
        $this->dispatcher->dispatch(new JemarcheDataSurveyEvent($this->jemarcheDataSurvey), SurveyEvents::JEMARCHE_DATA_SURVEY_ANSWERED);
        if ($this->jemarcheDataSurvey->getEmailAddress()) {
            $this->bus->dispatch(new JemarcheDataSurveyCreateCommand($this->jemarcheDataSurvey->getEmailAddress()));
        }
    }

    protected function getCustomDeserializeGroups(): array
    {
        return [self::DESERIALIZE_GROUP];
    }
}
