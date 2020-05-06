<?php

namespace App\Form;

use App\Entity\Adherent;
use App\Entity\Committee;
use App\Entity\CommitteeMembership;
use App\Repository\CommitteeRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Security;

class MyCommitteeChoiceType extends AbstractType
{
    private $security;
    private $repository;

    public function __construct(Security $security, CommitteeRepository $repository)
    {
        $this->security = $security;
        $this->repository = $repository;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'choices' => $this->getChoices(),
            'class' => Committee::class,
            'choice_label' => 'name',
            'choice_value' => 'uuid',
        ]);
    }

    public function getParent()
    {
        return EntityType::class;
    }

    private function getChoices(): array
    {
        $user = $this->security->getUser();

        if (!$user instanceof Adherent) {
            return [];
        }

        return $this->repository->findCommitteesByPrivilege($user, [CommitteeMembership::COMMITTEE_SUPERVISOR]);
    }
}
