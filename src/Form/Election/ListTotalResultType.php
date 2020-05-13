<?php

namespace App\Form\Election;

use App\Entity\Election\ListTotalResult;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class ListTotalResultType extends AbstractType implements DataTransformerInterface
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('id', HiddenType::class)
            ->add('listLabel', TextType::class, ['disabled' => true])
            ->add('total', IntegerType::class)
            ->addModelTransformer($this)
        ;
    }

    public function transform($value)
    {
        if ($value instanceof ListTotalResult) {
            return [
                'id' => $value->getId(),
                'listLabel' => $value->getList()->getLabel(),
                'total' => $value->getTotal(),
            ];
        }

        return [];
    }

    public function reverseTransform($value)
    {
        if (isset($value['id'])) {
            $object = $this->em->getReference(ListTotalResult::class, $value['id']);
            $object->setTotal((int) ($value['total'] ?? 0));

            return $object;
        }

        return $value;
    }
}
