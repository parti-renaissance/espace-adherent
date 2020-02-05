<?php

namespace AppBundle\Form;

use AppBundle\Assessor\AssessorRoleAssociationValueObject;
use AppBundle\Entity\VotePlace;
use AppBundle\Form\DataTransformer\EmailToAdherentTransformer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AssessorRoleAssociationType extends AbstractType implements DataTransformerInterface
{
    private $entityManager;
    private $adherentTransformer;

    public function __construct(EntityManagerInterface $entityManager, EmailToAdherentTransformer $adherentTransformer)
    {
        $this->entityManager = $entityManager;
        $this->adherentTransformer = $adherentTransformer;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('votePlace', HiddenType::class)
            ->add('adherent', EmailType::class, [
                'label' => false,
                'required' => false,
                'attr' => ['placeholder' => 'E-mail de l\'adhérent'],
            ])
        ;

        $builder->get('votePlace')->addModelTransformer($this);
        $builder->get('adherent')->addModelTransformer($this->adherentTransformer);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => AssessorRoleAssociationValueObject::class,
        ]);
    }

    public function transform($value)
    {
        if ($value instanceof VotePlace) {
            return $value->getId();
        }

        return $value;
    }

    public function reverseTransform($value)
    {
        if (is_numeric($value)) {
            return $this->entityManager->getPartialReference(VotePlace::class, $value);
        }

        return $value;
    }
}
