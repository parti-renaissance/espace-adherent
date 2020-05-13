<?php

namespace App\Form;

use App\Entity\ReferentOrganizationalChart\ReferentPersonLink;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Security;

class ReferentPersonLinkForCoReferentType extends AbstractType
{
    private $user;

    public function __construct(Security $security)
    {
        $this->user = $security->getUser();
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            /** @var ReferentPersonLink $referentPersonLink */
            $referentPersonLink = $event->getData();
            $form = $event->getForm();
            $options = [
                'required' => false,
            ];

            if ($referentPersonLink->getAdherent() && $referent = $referentPersonLink->getAdherent()->getReferentOfReferentTeam()) {
                if ($referent !== $this->user) {
                    $options = array_merge($options, ['disabled' => true]);
                }
            }

            $form->add('isCoReferent', CheckboxType::class, $options);
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ReferentPersonLink::class,
        ]);
    }
}
