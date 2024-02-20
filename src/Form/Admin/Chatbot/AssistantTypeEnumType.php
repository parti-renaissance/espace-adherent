<?php

namespace App\Form\Admin\Chatbot;

use App\Chatbot\Enum\AssistantTypeEnum;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AssistantTypeEnumType extends AbstractType
{
    public function getParent()
    {
        return EnumType::class;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'class' => AssistantTypeEnum::class,
                'choice_label' => static function (AssistantTypeEnum $assistantType): string {
                    return 'chatbot.assistant_type.'.$assistantType->value;
                },
            ])
        ;
    }
}
