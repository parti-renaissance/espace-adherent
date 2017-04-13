<?php

namespace AppBundle\Form;

use AppBundle\Entity\ProcurationProxy;
use AppBundle\Repository\ProcurationProxyRepository;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;

class ProcurationProxyType extends AbstractProcurationType
{
    private $repository;

    public function __construct(ProcurationProxyRepository $repository)
    {
        $this->repository = $repository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('voteCountry', UnitedNationsCountryType::class)
            ->add('votePostalCode', TextType::class, [
                'required' => false,
            ])
            ->add('voteCity', HiddenType::class, [
                'required' => false,
                'error_bubbling' => true,
            ])
            ->add('voteCityName', TextType::class, [
                'required' => false,
            ])
            ->add('voteOffice', TextType::class, [
                'required' => false,
            ])
            ->add('electionPresidentialFirstRound', CheckboxType::class, [
                'required' => false,
            ])
            ->add('electionPresidentialSecondRound', CheckboxType::class, [
                'required' => false,
            ])
            ->add('electionLegislativeFirstRound', CheckboxType::class, [
                'required' => false,
            ])
            ->add('electionLegislativeSecondRound', CheckboxType::class, [
                'required' => false,
            ])
            ->add('electionLegislativeSecondRound', CheckboxType::class, [
                'required' => false,
            ])
            ->add('inviteSourceName', TextType::class, [
                'required' => false,
            ])
            ->add('inviteSourceFirstName', TextType::class, [
                'required' => false,
            ])
            ->add('conditions', CheckboxType::class, [
                'required' => false,
                'mapped' => false,
                'constraints' => new IsTrue([
                    'message' => 'procuration.proposal_conditions.required',
                    'groups' => ['front'],
                ]),
            ])
            ->add('authorization', CheckboxType::class, [
                'mapped' => false,
                'constraints' => new IsTrue([
                    'message' => 'procuration.authorization.required',
                    'groups' => ['front'],
                ]),
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setDefault('data_class', ProcurationProxy::class);
        $resolver->setDefault('validation_groups', ['front']);
        $resolver->setDefault('procuration_unique_message', 'procuration.proposal.unique');
    }

    public function getBlockPrefix(): string
    {
        return 'app_procuration_proposal';
    }

    protected function matchEmailAddress(string $emailAddress): bool
    {
        return count($this->repository->findByEmailAddress($emailAddress)) > 0;
    }
}
