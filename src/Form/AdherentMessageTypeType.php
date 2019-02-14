<?php

namespace AppBundle\Form;

use AppBundle\AdherentMessage\AdherentMessageTypeEnum;
use AppBundle\Entity\Adherent;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class AdherentMessageTypeType extends AbstractType
{
    private $tokenStorage;
    private $choices = [];

    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        if ($this->choices) {
            if (\count($this->choices) > 1) {
                $resolver->setDefault('choices', $this->choices);
            } else {
                $resolver->setDefaults([
                    'data' => current($this->choices),
                    'label' => false,
                ]);
            }
        }
    }

    public function getParent(): string
    {
        $adherent = $this->tokenStorage->getToken()->getUser();

        if (!$adherent instanceof Adherent) {
            return parent::getParent();
        }

        $this->buildChoices($adherent);

        if (empty($this->choices)) {
            return parent::getParent();
        } elseif (1 === \count($this->choices)) {
            return HiddenType::class;
        }

        return ChoiceType::class;
    }

    private function buildChoices(Adherent $adherent): void
    {
        $this->choices = [];

        foreach ([AdherentMessageTypeEnum::REFERENT] as $type) {
            if (method_exists($adherent, $methodName = 'is'.ucfirst($type)) && $adherent->{$methodName}()) {
                $this->choices['adherent_message.'.$type] = $type;
            }
        }
    }
}
