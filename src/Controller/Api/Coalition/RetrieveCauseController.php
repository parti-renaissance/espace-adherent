<?php

namespace App\Controller\Api\Coalition;

use App\Entity\Coalition\Cause;
use App\Repository\Coalition\CauseRepository;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class RetrieveCauseController
{
    private $causeRepository;

    public function __construct(CauseRepository $causeRepository)
    {
        $this->causeRepository = $causeRepository;
    }

    public function __invoke(string $id): ?Cause
    {
        if (false === Uuid::isValid($id)) {
            $data = $this->causeRepository->findOneBy(['slug' => $id]);
        } else {
            $data = $this->causeRepository->findOneByUuid($id);
        }

        if (null === $data) {
            throw new NotFoundHttpException(\sprintf('Cause with id or slug %s not found.', $id));
        }

        return $data;
    }
}
