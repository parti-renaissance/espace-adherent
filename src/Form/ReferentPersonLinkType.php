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
            $form = $event->getForm();
            if (!$referentPersonLink->getAdherent()) {
                $form
                    ->add('email', TextType::class, [
                        'label' => 'E-mail',
                        'form_full' => true,
                    ])
                ;

                return;
            }

            $options = [
                'required' => false,
            ];

            if ($referentPersonLink->getAdherent() && $referent = $referentPersonLink->getAdherent()->getReferentOfReferentTeam()) {
                if ($referent !== $this->user) {
                    $referentPersonLink->setIsCoReferent(false);
                    $options = array_merge($options, ['disabled' => true]);
                } else {
                    $referentPersonLink->setIsCoReferent(true);
                }
            }

            $form
                ->add('email', TextType::class, [
                    'label' => 'E-mail',
                    'form_full' => true,
                    'disabled' => $referentPersonLink->isCoReferent(),
                ])
                ->add('isCoReferent', CheckboxType::class, $options)
            ;
        });

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $data = $event->getData();
            $object = $event->getForm()->getData();

            if ($event->getForm()->has('isCoReferent') && isset($data['email']) && $data['email'] !== $object->getEmail()) {
                $data['isCoReferent'] = false;
                $event->setData($data);
            }
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
