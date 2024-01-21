<?php

namespace App\Form\Admin\Chatbot;

use App\Chatbot\Enum\ChatbotTypeEnum;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ChatbotTypeEnumType extends AbstractType
{
    public function getParent()
    {
        return EnumType::class;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'class' => ChatbotTypeEnum::class,
                'choice_label' => static function (ChatbotTypeEnum $type): string {
                    return 'chatbot.type.'.$type->value;
                },
            ])
        ;
    }
}
