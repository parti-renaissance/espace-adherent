<?php

namespace AppBundle\Mailchimp\Webhook\Handler;

use AppBundle\Entity\Adherent;
use AppBundle\Repository\AdherentRepository;

abstract class AbstractAdherentHandler implements WebhookHandlerInterface
{
    /** @var AdherentRepository */
    private $repository;

    /**
     * @required
     */
    public function setRepository(AdherentRepository $repository): void
    {
        $this->repository = $repository;
    }

    protected function getAdherent(string $email): ?Adherent
    {
        return $this->repository->findOneByEmail($email);
    }
}
