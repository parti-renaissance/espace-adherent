<?php

namespace App\Form;

use App\Entity\Committee;
use App\Repository\CommitteeRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;

class CommitteeUuidType extends AbstractType
{
    private $committeeRepository;

    public function __construct(CommitteeRepository $committeeRepository)
    {
        $this->committeeRepository = $committeeRepository;
    }

    public function getParent()
    {
        return HiddenType::class;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->addViewTransformer(new CallbackTransformer(
                function ($committee) {
                    return $committee instanceof Committee ? $committee->getUuid()->toString() : null;
                },
                function ($uuid) {
                    $committee = $this->committeeRepository->findOneByUuid($uuid);

                    if (!$committee) {
                        throw new TransformationFailedException("A Committee with uuid \"$uuid\" does not exist.");
                    }

                    return $committee;
                }
            ))
        ;
    }
}
