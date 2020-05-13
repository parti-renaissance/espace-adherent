<?php

namespace App\Form\DataTransformer;

use App\Entity\ReferentArea;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\PersistentCollection;
use Symfony\Component\Form\DataTransformerInterface;

class ReferentAreaTransformer implements DataTransformerInterface
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function transform($arrayReferentArea)
    {
        $newArray = [];

        if (!($arrayReferentArea instanceof PersistentCollection)) {
            return '';
        }

        foreach ($arrayReferentArea as $key => $value) {
            /* @var $value ReferentArea */
            $newArray[] = $value->getAreaCode();
        }

        return implode(', ', $newArray);
    }

    public function reverseTransform($strAreasCodes)
    {
        $newArray = [];

        $array = array_map('trim', explode(',', $strAreasCodes));

        foreach ($array as $key => $value) {
            $item = $this->em
                ->getRepository(ReferentArea::class)
                ->findOneBy(['areaCode' => $value])
            ;

            if (!\is_null($item)) {
                $newArray[$key] = $item;
            }
        }

        return new PersistentCollection($this->em, ReferentArea::class, new ArrayCollection($newArray));
    }
}
