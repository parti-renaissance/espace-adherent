<?php

namespace App\Form\TerritorialCouncil;

use App\Form\DataTransformer\TerritorialCouncilMembershipToUuidTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;

class CandidacyInvitedMembershipType extends AbstractType
{
    private $dataTransformer;

    public function __construct(TerritorialCouncilMembershipToUuidTransformer $dataTransformer)
    {
        $this->dataTransformer = $dataTransformer;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addModelTransformer($this->dataTransformer);
    }

    public function getParent()
    {
        return HiddenType::class;
    }
}
