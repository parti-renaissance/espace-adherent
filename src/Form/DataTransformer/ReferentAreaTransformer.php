<?php

namespace AppBundle\Form\DataTransformer;

use AppBundle\Entity\ReferentArea;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\PersistentCollection;
use Symfony\Component\Form\DataTransformerInterface;

class ReferentAreaTransformer implements DataTransformerInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * ReferentAreaTransformer constructor.
     *
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * {@inheritdoc}
     */
    public function transform($arrayReferentArea)
    {
        $newArray = array();

        if (!($arrayReferentArea instanceof PersistentCollection)) {
            return '';
        }

        foreach ($arrayReferentArea as $key => $value) {
            $newArray[] = $value;
        }

        return implode(', ', $newArray);
    }

    /**
     * {@inheritdoc}
     */
    public function reverseTransform($strAreasCodes)
    {
        $newArray = array();

        $array = array_map('trim', explode(',', $strAreasCodes));

        foreach ($array as $key => $value) {
            $item = $this->em
                ->getRepository(ReferentArea::class)
                ->findOneBy(array('areaCode' => $value));

            if (!is_null($item)) {
                $newArray[$key] = $item;
            }
        }

        return new PersistentCollection($this->em, ReferentArea::class, new ArrayCollection($newArray));
    }
}