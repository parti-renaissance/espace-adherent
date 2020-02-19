<?php

namespace AppBundle\Form;

use AppBundle\Entity\VotePlace;
use AppBundle\Repository\VotePlaceRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AssociationVotePlaceType extends AbstractType implements DataTransformerInterface
{
    private $votePlaceRepository;

    public function __construct(VotePlaceRepository $votePlaceRepository)
    {
        $this->votePlaceRepository = $votePlaceRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('votePlace', HiddenType::class)
            ->add('alias', TextType::class, [
                'required' => false,
                'label' => false,
            ])
            ->addModelTransformer($this)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['label' => false]);
    }

    public function transform($value)
    {
        if ($value instanceof VotePlace) {
            return [
                'votePlace' => $value->getId(),
                'alias' => $value->getAlias() ?? $value->getName(),
            ];
        }

        return $value;
    }

    public function reverseTransform($value)
    {
        if (isset($value['votePlace']) && is_numeric($value['votePlace'])) {
            /** @var VotePlace $votePlace */
            $votePlace = $this->votePlaceRepository->find($value['votePlace']);

            if (isset($value['alias']) && $value['alias'] !== $votePlace->getName()) {
                $votePlace->setAlias($value['alias']);
            }

            return $votePlace;
        }

        return $value;
    }
}
