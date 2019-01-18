<?php

namespace AppBundle\Form;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\ReferentTag;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class ReferentTagChoiceType extends AbstractType
{
    private $tokenStorage;

    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'choices' => $this->getChoices(),
            'expanded' => true,
            'multiple' => true,
        ]);
    }

    public function getParent()
    {
        return ChoiceType::class;
    }

    private function getChoices(): array
    {
        $token = $this->tokenStorage->getToken();

        if (!$token || !($user = $token->getUser()) instanceof Adherent) {
            return [];
        }

        /** @var Adherent $user */
        return array_merge(...array_map(function (ReferentTag $tag) {
            return [$tag->getName() => $tag->getCode()];
        }, $user->getManagedArea()->getTags()->toArray()));
    }
}
