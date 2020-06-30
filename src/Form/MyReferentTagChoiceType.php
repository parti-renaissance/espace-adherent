<?php

namespace App\Form;

use App\Entity\Adherent;
use App\Entity\MyTeam\DelegatedAccess;
use App\Entity\ReferentTag;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Security;

class MyReferentTagChoiceType extends AbstractType
{
    private $security;

    private $requestStack;

    public function __construct(Security $security, RequestStack $requestStack)
    {
        $this->security = $security;
        $this->requestStack = $requestStack;
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

        if (!$user || !$user instanceof Adherent) {
            return [];
        }

        if ($delegatedAccess = $user->getReceivedDelegatedAccessByUuid($this->requestStack->getMasterRequest()->attributes->get(DelegatedAccess::ATTRIBUTE_KEY))) {
            $user = $delegatedAccess->getDelegator();
        }

        if (!$user->isReferent()) {
            return [];
        }

        return $user->getManagedArea()->getTags()->toArray();
    }
}
