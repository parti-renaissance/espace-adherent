<?php

namespace AppBundle\Form\DataTransformer;

use AppBundle\Entity\CitizenProjectSkill;
use AppBundle\Repository\CitizenProjectSkillRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Form\DataTransformerInterface;

class CitizenProjectSkillTransformer implements DataTransformerInterface
{
    private $citizenProjectSkillRepository;

    public function __construct(CitizenProjectSkillRepository $citizenProjectSkillRepository)
    {
        $this->citizenProjectSkillRepository = $citizenProjectSkillRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function transform($arrayProjectCitizenSkill)
    {
        foreach ($arrayProjectCitizenSkill as $value) {
            /* @var $value CitizenProjectSkill */
            $newArray[] = [
              'id' => $value->getId(),
              'name' => $value->getName(),
            ];
        }

        return $newArray ?? [];
    }

    /**
     * {@inheritdoc}
     */
    public function reverseTransform($ids)
    {
        $collection = new ArrayCollection();
        foreach ($ids as $id) {
            if ($skill = $this->citizenProjectSkillRepository->find($id)) {
                $collection->add($skill);
            }
        }

        return $collection;
    }
}
