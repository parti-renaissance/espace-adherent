<?php

namespace AppBundle\Form;

use AppBundle\AdherentMessage\Filter\ReferentFilterDataObject;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\ReferentTag;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class AdherentMessageReferentFilterType extends AbstractType
{
    private $tokenStorage;

    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('zones', ReferentTagChoiceType::class, ['choices' => $this->buildChoices()]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ReferentFilterDataObject::class,
        ]);
    }

    private function buildChoices(): array
    {
        $token = $this->tokenStorage->getToken();

        if (!$token || !($user = $token->getUser()) instanceof Adherent) {
            return [];
        }

        /** @var Adherent $user */
        return array_merge(
            ...array_map(
                function (ReferentTag $tag) {
                    return [$tag->getName() => $tag->getExternalId()];
                },
                array_filter(
                    $user->getManagedArea()->getTags()->toArray(),
                    function (ReferentTag $tag) { return (bool) $tag->getExternalId(); }
                )
            )
        );
    }
}
