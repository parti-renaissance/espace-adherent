<?php

namespace App\Form;

use App\Summary\SummaryItemDisplayOrderer;
use App\Summary\SummaryItemPositionableInterface;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SummaryItemPositionType extends AbstractType implements DataMapperInterface
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var SummaryItemPositionableInterface $item */
        $item = $options['item'];
        /** @var Collection|SummaryItemPositionableInterface[] $collection */
        $collection = $options['collection'];
        $count = $collection->count();

        if (0 < $count) {
            $builder->add('entry', ChoiceType::class, [
                'label' => false,
                'choices' => range(1, $item ? $count : ++$count),
                'choice_label' => function ($choice) {
                    return $choice;
                },
                'data' => $item ? $item->getDisplayOrder() : $count,
            ]);
        } else {
            $builder->add('entry', HiddenType::class, [
                'label' => false,
                'data' => 1,
            ]);
        }

        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) use ($item, $collection) {
            $form = $event->getForm();
            $oldPosition = $form->getData();
            $newPosition = $form->get('entry')->getData() ?: 1;

            if ($collection) {
                if ($item && $oldPosition !== $newPosition) {
                    SummaryItemDisplayOrderer::updateItem($collection, $item, $oldPosition, $newPosition);
                } elseif (!$item) {
                    SummaryItemDisplayOrderer::insertItem($collection, $newPosition);
                }
            }
        });

        $builder->setDataMapper($this);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setRequired(['collection', 'item'])
            ->setAllowedTypes('collection', ['null', Collection::class])
            ->setAllowedTypes('item', ['null', SummaryItemPositionableInterface::class])
        ;
    }

    public function mapDataToForms($data, $forms)
    {
        $forms = iterator_to_array($forms);

        $forms['entry']->setData($data);
    }

    public function mapFormsToData($forms, &$data)
    {
        $forms = iterator_to_array($forms);

        $data = $forms['entry']->getData();
    }
}
