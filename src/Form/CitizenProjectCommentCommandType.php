<?php

namespace AppBundle\Form;

use AppBundle\CitizenProject\CitizenProjectCommentCommand;
use AppBundle\CitizenProject\CitizenProjectPermissions;
use AppBundle\Entity\CitizenProject;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class CitizenProjectCommentCommandType extends AbstractType
{
    private $authorizationChecker;

    public function __construct(AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->authorizationChecker = $authorizationChecker;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('content', TextType::class)
        ;

        $data = $builder->getData();

        if ($data instanceof CitizenProjectCommentCommand && $this->canAdministrateProject($data->getCitizenProject())) {
            $builder
                ->add('sendMail', CheckboxType::class, [
                    'required' => false,
                    'label' => 'Envoyer aussi par e-mail',
                    'attr' => ['class' => 'form__checkbox form__checkbox--large'],
                ])
            ;
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => CitizenProjectCommentCommand::class,
        ]);
    }

    private function canAdministrateProject(CitizenProject $project): bool
    {
        return $this->authorizationChecker->isGranted(CitizenProjectPermissions::ADMINISTRATE, $project);
    }
}
