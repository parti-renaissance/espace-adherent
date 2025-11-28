<?php

declare(strict_types=1);

namespace App\Form\Committee;

use App\Entity\CommitteeMembership;
use App\Form\DataTransformer\UuidToObjectTransformer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;

class CommitteeCandidacyInvitedMembershipType extends AbstractType
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addModelTransformer(new UuidToObjectTransformer($this->entityManager, CommitteeMembership::class));
    }

    public function getParent(): string
    {
        return HiddenType::class;
    }
}
