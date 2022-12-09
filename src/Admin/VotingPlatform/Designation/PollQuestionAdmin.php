<?php

namespace App\Admin\VotingPlatform\Designation;

use App\Admin\AbstractAdmin;
use App\Form\Admin\VotingPlatform\Poll\QuestionChoiceType;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;

class PollQuestionAdmin extends AbstractAdmin
{
    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->add('content', null, ['label' => 'Question'])
            ->add('choices', CollectionType::class, [
                'label' => 'Choix de réponse',
                'error_bubbling' => false,
                'by_reference' => false,
                'allow_add' => true,
                'allow_delete' => true,
                'entry_type' => QuestionChoiceType::class,
            ])
            ->add('position', IntegerType::class, ['label' => 'Position', 'attr' => ['min' => 1, 'step' => 1]])
        ;
    }
}
