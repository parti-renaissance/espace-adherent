<?php

namespace App\OAuth\Form;

use App\OAuth\Model\Scope;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Translation\TranslatorInterface;

class ScopesType extends AbstractType
{
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        foreach (Scope::toArray() as $scope) {
            $builder->add($scope, CheckboxType::class, [
                'required' => false,
                'label' => $scope.' ('.$this->translator->trans($scope, [], 'oauth').')',
            ]);
        }

        $builder->addModelTransformer(new class() implements DataTransformerInterface {
            public function transform($value)
            {
                if (!$value) {
                    return;
                }

                $data = [];

                foreach (Scope::toArray() as $scope) {
                    $data[$scope] = \in_array($scope, $value, true);
                }

                return $data;
            }

            public function reverseTransform($value)
            {
                return array_keys($value, true, true);
            }
        });
    }
}
