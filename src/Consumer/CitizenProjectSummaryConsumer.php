<?php

namespace AppBundle\Consumer;

use AppBundle\CitizenProject\CitizenProjectBroadcaster;
use AppBundle\Entity\Adherent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CitizenProjectSummaryConsumer extends AbstractConsumer
{
    private $broadcaster;

    public function __construct(
        ValidatorInterface $validator,
        EntityManagerInterface $manager,
        CitizenProjectBroadcaster $broadcaster
    ) {
        parent::__construct($validator, $manager);

        $this->broadcaster = $broadcaster;
    }

    protected function configureDataConstraints(): array
    {
        return [
            'adherent_uuid' => [new Assert\NotBlank()],
            'approved_since' => [new Assert\NotBlank()],
        ];
    }

    protected function doExecute(array $data): int
    {
        try {
            $adherent = $this->manager->getRepository(Adherent::class)->findOneByUuid($data['adherent_uuid']);

            if (null === $adherent) {
                $this->writeln('Adherent not found', 'Adherent with '.$data['adherent_uuid'].' uuid not found');
                $this->getLogger()->error('Adherent with '.$data['adherent_uuid'].' not found', $data);

                return self::MSG_ACK;
            }

            $this->broadcaster->broadcast($adherent, $data['approved_since']);

            return self::MSG_ACK;
        } catch (\Exception $e) {
            $this->getLogger()->error('Consumer failed', ['exception' => $e]);

            throw $e;
        }
    }
}
