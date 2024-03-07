<?php

namespace App\Form\Procuration\V2;

use App\Procuration\V2\Command\RequestCommand;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RequestType extends AbstractProcurationType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => RequestCommand::class,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'procuration_request';
    }
}
