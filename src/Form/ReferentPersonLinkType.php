<?php

namespace AppBundle\Form;

use AppBundle\Entity\ReferentOrganizationalChart\ReferentPersonLink;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Security;

class ReferentPersonLinkType extends AbstractType
{
    private $user;

    public function __construct(Security $security)
    {
        $this->user = $security->getUser();
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstName', TextType::class, [
                'label' => 'Prénom',
                'form_full' => true,
            ])
            ->add('lastName', TextType::class, [
                'label' => 'Nom',
                'form_full' => true,
            ])
            ->add('email', TextType::class, [
                'label' => 'E-mail',
                'form_full' => true,
            ])
            ->add('phone', TextType::class, [
                'label' => 'Téléphone',
                'form_full' => true,
            ])
            ->add('postalAddress', TextType::class, [
                'label' => 'Adresse postale',
                'form_full' => true,
            ])
        ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            /** @var ReferentPersonLink $referentPersonLink */
            $referentPersonLink = $event->getData();
            if (!$referentPersonLink->getAdherent()) {
                return;
            }

            $form = $event->getForm();
            $options = [
                'required' => false,
            ];

            if ($referentPersonLink->getAdherent() && $referent = $referentPersonLink->getAdherent()->getReferentTeamReferent()) {
                if ($referent !== $this->user) {
                    $referentPersonLink->setIsCoReferent(false);
                    $options = array_merge($options, ['disabled' => true]);
                } else {
                    $referentPersonLink->setIsCoReferent(true);
                }
            }

            $form->add('isCoReferent', CheckboxType::class, $options);
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'label' => false,
            'data_class' => ReferentPersonLink::class,
        ]);
    }
}
