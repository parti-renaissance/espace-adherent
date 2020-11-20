<?php

namespace App\Form\TerritorialCouncil;

use App\Entity\TerritorialCouncil\TerritorialCouncilMembership;
use App\Form\DataTransformer\UuidToObjectTransformer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;

class CandidacyInvitedMembershipType extends AbstractType
{
    private $entityManager;

    public function __construct(EntityManagerInterface $dataTransformer)
    {
        $this->entityManager = $dataTransformer;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addModelTransformer(new UuidToObjectTransformer($this->entityManager, TerritorialCouncilMembership::class));
    }

    public function getParent()
    {
        return HiddenType::class;
    }
}
