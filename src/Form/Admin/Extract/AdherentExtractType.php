<?php

namespace App\Form\Admin\Extract;

use App\Adherent\AdherentExtractCommand;
use App\Adherent\AdherentExtractCommandHandler;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AdherentExtractType extends AbstractEmailExtractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => AdherentExtractCommand::class,
        ]);
    }

    protected function getFieldChoices(): array
    {
        return AdherentExtractCommand::getFieldChoices();
    }

    protected function getTranslationPrefix(): string
    {
        return AdherentExtractCommandHandler::getTranslationPrefix();
    }
}
