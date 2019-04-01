<?php

namespace AppBundle\Form;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\ReferentTag;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Security;

class MyReferentTagChoiceType extends AbstractType
{
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'choices' => $this->getChoices(),
            'class' => ReferentTag::class,
            'choice_label' => 'name',
        ]);
    }

    public function getParent()
    {
        return EntityType::class;
    }

    private function getChoices(): array
    {
        $user = $this->security->getUser();

        if (!$user || !$user instanceof Adherent || !$user->getManagedArea()) {
            return [];
        }

        return $user->getManagedArea()->getTags()->toArray();
    }
}
