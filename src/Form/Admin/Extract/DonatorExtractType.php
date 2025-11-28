<?php

declare(strict_types=1);

namespace App\Form\Admin\Extract;

use App\Donation\Command\DonatorExtractCommand;
use App\Donation\Handler\DonatorExtractCommandHandler;
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
