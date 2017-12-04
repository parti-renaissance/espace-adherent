<?php

namespace AppBundle\Consumer;

use AppBundle\CitizenProject\CitizenProjectManager;
use AppBundle\CitizenProject\CitizenProjectMessageNotifier;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\CitizenProject;
use AppBundle\Repository\CitizenProjectRepository;
use OldSound\RabbitMqBundle\RabbitMq\ProducerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints as Assert;

class ProjectCitizenCreationNotificationConsumer extends AbstractConsumer
{
    /**
     * @var CitizenProjectMessageNotifier
     */
    private $citizenProjectMessageNotifier;

    /**
     * @var CitizenProjectManager
     */
    private $citizenProjectManager;

    /**
     * @var CitizenProjectRepository
     */
    private $citizenProjectRepository;

    /**
     * @var ProducerInterface
     */
    private $producer;

    /**
     * Creates a list of constraints to validate the message.
     *
     * @return Constraint[]
     */
    protected function configureDataConstraints(): array
    {
        return [
            'uuid' => [new Assert\NotBlank()],
            'offset' => [new Assert\NotBlank()],
        ];
    }

    /**
     * Once the data validated, executes the real message.
     */
    protected function doExecute(array $data): int
    {
        /** @var CitizenProject $citizenProject */
        $citizenProject = $this->citizenProjectRepository->findOneByUuid($data['uuid']);

        if (null === $citizenProject) {
            $this->writeln('citizen project not found', 'citizen project with '.$data['uuid'].' uuid not found');
            $this->getLogger()->error('citizen project with '.$data['uuid'].' not found', $data);

            return self::MSG_ACK;
        }

        $creator = $this->citizenProjectManager->getCitizenProjectCreator($citizenProject);
        $offset = $data['offset'];
        $totalAdherent = $this->citizenProjectManager->findAdherentNearCitizenProjectOrAcceptAllNotification($citizenProject, $offset)->count();

        if (!$totalAdherent) {
            $this->writeln('info', 'No adherent to notify found for '.$data['uuid'].' citizen project');

            return self::MSG_ACK;
        }

        $this->writeln('info', sprintf('Start sending. offset : %s | totalAdherent : %s | citizenProjectUuid %s', $offset, $totalAdherent, $citizenProject->getUuid()->toString()));

        try {
            do {
                $adherents = $this->citizenProjectManager->findAdherentNearCitizenProjectOrAcceptAllNotification($citizenProject, $offset)->getIterator();

                /** @var Adherent $adherent */
                foreach ($adherents as $adherent) {
                    $this->citizenProjectMessageNotifier->sendAdherentNotificationCreation($adherent, $citizenProject, $creator);
                }

                $offset += CitizenProjectMessageNotifier::NOTIFICATION_PER_PAGE;
            } while ($offset <= $totalAdherent);

            $this->writeln('success', 'Message correctly send from offset '.$data['offset'].' to the end');
        } catch (\Exception $e) {
            $this->writeln('Error during preparing message', $e->getMessage());
            $this->getLogger()->error('Error during preparing message', [$data, $e]);

            $this->producer->publish(\GuzzleHttp\json_encode([
                'uuid' => $data['uuid'],
                'offset' => $offset,
            ]));
        }

        return self::MSG_ACK;
    }

    public function setCitizenProjectMessageNotifier(CitizenProjectMessageNotifier $citizenProjectMessageNotifier): void
    {
        $this->citizenProjectMessageNotifier = $citizenProjectMessageNotifier;
    }

    public function setCitizeProjectManager(CitizenProjectManager $citizenProjectManager): void
    {
        $this->citizenProjectManager = $citizenProjectManager;
    }

    public function setCitizenProjectRepository(CitizenProjectRepository $citizenProjectRepository): void
    {
        $this->citizenProjectRepository = $citizenProjectRepository;
    }

    public function setProducer(ProducerInterface $producer): void
    {
        $this->producer = $producer;
    }
}
