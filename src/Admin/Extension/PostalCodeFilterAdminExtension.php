<?php

namespace App\Admin\Extension;

use Sonata\AdminBundle\Admin\AbstractAdminExtension;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Filter\Model\FilterData;
use Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery;
use Sonata\DoctrineORMAdminBundle\Filter\CallbackFilter;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class PostalCodeFilterAdminExtension extends AbstractAdminExtension
{
    public function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('postalCode', CallbackFilter::class, [
                'label' => 'Code postal (préfixe)',
                'show_filter' => true,
                'field_type' => TextType::class,
                'callback' => function (ProxyQuery $qb, string $alias, string $field, FilterData $value) {
                    if (!$value->hasValue()) {
                        return false;
                    }

                    $value = array_map('trim', explode(',', strtolower($value->getValue())));
                    $postalCodeExpression = $qb->expr()->orX();
                    foreach (array_filter($value) as $key => $code) {
                        $postalCodeExpression->add(sprintf('%s.postAddress.postalCode LIKE :postalCode_%s', $alias, $key));
                        $qb->setParameter('postalCode_'.$key, $code.'%');
                    }

                    $qb->andWhere($postalCodeExpression);

                    return true;
                },
            ])
        ;
    }
}
