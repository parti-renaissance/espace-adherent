<?php

declare(strict_types=1);

namespace App\Form\DataTransformer;

use App\Entity\Committee;
use App\Repository\CommitteeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Form\DataTransformerInterface;

class CommitteeTransformer implements DataTransformerInterface
{
    private $committeeRepository;

    public function __construct(CommitteeRepository $committeeRepository)
    {
        $this->committeeRepository = $committeeRepository;
    }

    public function transform($collection): mixed
    {
        if (!is_iterable($collection)) {
            return [];
        }

        /** @var Committee $committee */
        foreach ($collection as $committee) {
            $uuids[] = [
                'uuid' => $committee->getUuid()->toString(),
                'name' => $committee->getName(),
            ];
        }

        return $uuids ?? [];
    }

    public function reverseTransform($uuids): mixed
    {
        $collection = new ArrayCollection();

        foreach ($uuids as $uuid) {
            if ($committee = $this->committeeRepository->findOneByUuid($uuid)) {
                $collection->add($committee);
            }
        }

        return $collection;
    }
}
