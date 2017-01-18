<?php

namespace AppBundle\Form;

use AppBundle\Entity\Adherent;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\ChoiceList\Loader\CallbackChoiceLoader;
use Symfony\Component\Form\Exception\InvalidConfigurationException;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/*
 * Basic form allowing an adherent to pin interests.
 *
 * This forms requires an Adherent as underlying data, so if needed, it can be nested
 * in another form using an Adherent but then the option "inherit_data" must be true.
 * See http://symfony.com/doc/current/form/inherit_data_option.html
 */
class AdherentInterestsFormType extends AbstractType
{
    private $lazyInterestsChoices;

    public function __construct(array $interests)
    {
        $this->lazyInterestsChoices = function () use ($interests) {
            return array_flip($interests);
        };
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if (!$builder->getData() instanceof Adherent) {
            throw new InvalidConfigurationException(sprintf('The form type "%s" requires a pre set "%s" as underlying data.', __CLASS__, Adherent::class));
        }

        $builder->add('interests', ChoiceType::class, [
            'choice_loader' => new CallbackChoiceLoader($this->lazyInterestsChoices),
            'expanded' => true,
            'multiple' => true,
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Adherent::class,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'app_adherent_pin_interests';
    }
}
