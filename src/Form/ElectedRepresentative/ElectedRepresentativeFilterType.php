<?php

namespace App\Form\ElectedRepresentative;

use App\ElectedRepresentative\Filter\ListFilter;
use App\Entity\ReferentTag;
use App\Form\MyReferentTagChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ElectedRepresentativeFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('sort', HiddenType::class, ['required' => false])
            ->add('order', HiddenType::class, ['required' => false])
            ->add('referentTags', MyReferentTagChoiceType::class, [
                'placeholder' => 'Tous',
                'required' => false,
                'by_reference' => false,
            ])
        ;

        $referentTagsField = $builder->get('referentTags');

        $referentTagsField->addModelTransformer(new CallbackTransformer(
                static function ($value) use ($referentTagsField) {
                    if (\is_array($value) && \count($value) === \count($referentTagsField->getOption('choices'))) {
                        return null;
                    }

                    return $value;
                },
                static function ($value) use ($referentTagsField) {
                    if (null === $value) {
                        return  $referentTagsField->getOption('choices');
                    }

                    if ($value instanceof ReferentTag) {
                        return [$value];
                    }

                    return $value;
                },
            ));
    }

    public function getBlockPrefix()
    {
        return 'f';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => ListFilter::class,
            ])
        ;
    }
}
