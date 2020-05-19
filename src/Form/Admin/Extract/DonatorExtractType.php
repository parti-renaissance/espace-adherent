<?php

namespace App\Form\Admin\Extract;

use App\Donation\DonatorExtractCommand;
use App\Donation\DonatorExtractCommandHandler;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DonatorExtractType extends AbstractEmailExtractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => DonatorExtractCommand::class,
        ]);
    }

    protected function getFieldChoices(): array
    {
        return DonatorExtractCommand::getFieldChoices();
    }

    protected function getTranslationPrefix(): string
    {
        return DonatorExtractCommandHandler::getTranslationPrefix();
    }
}
