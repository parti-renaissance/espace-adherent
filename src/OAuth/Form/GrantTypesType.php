<?php

namespace App\OAuth\Form;

use App\OAuth\Model\GrantTypeEnum;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;

class GrantTypesType extends AbstractType implements DataTransformerInterface
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        foreach (GrantTypeEnum::GRANT_TYPES_ENABLED as $grantType) {
            $builder->add($grantType, CheckboxType::class, ['required' => false]);
        }

        $builder->addModelTransformer($this);
    }

    public function transform($value)
    {
        if (!$value) {
            return;
        }

        $data = [];

        foreach (GrantTypeEnum::GRANT_TYPES_ENABLED as $grantType) {
            $data[$grantType] = \in_array($grantType, $value, true);
        }

        return $data;
    }

    public function reverseTransform($value)
    {
        return array_keys($value, true, true);
    }
}
