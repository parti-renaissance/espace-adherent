<?php

namespace AppBundle\Form;

use AppBundle\Entity\ProcurationRequest;
use AppBundle\Repository\ProcurationRequestRepository;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProcurationProfileType extends AbstractProcurationType
{
    private $repository;

    public function __construct(ProcurationRequestRepository $repository)
    {
        $this->repository = $repository;
    }

    protected function matchEmailAddress(string $emailAddress): bool
    {
        return count($this->repository->findByEmailAddress($emailAddress)) > 0;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setDefault('data_class', ProcurationRequest::class);
        $resolver->setDefault('validation_groups', ['vote', 'profile']);
        $resolver->setDefault('procuration_unique_message', 'procuration.request.unique');
    }

    public function getBlockPrefix(): string
    {
        return 'app_procuration_profile';
    }
}
