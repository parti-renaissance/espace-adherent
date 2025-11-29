<?php

namespace App\Form\Admin;

use App\Adherent\Tag\TagEnum;
use App\Entity\Adherent;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Form\Type\ModelAutocompleteType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RenaissanceAdherentAutocompleteType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'property' => ['firstName', 'lastName', 'emailAddress'],
            'class' => Adherent::class,
            'callback' => [self::class, 'filterCallback'],
            'to_string_callback' => fn (Adherent $adherent) => \sprintf('%s (%s)', $adherent->getFullName(), $adherent->getPostalCode()),
            'minimum_input_length' => 1,
        ]);
    }

    public static function filterCallback(AbstractAdmin $admin, array $property, $value): void
    {
        $datagrid = $admin->getDatagrid();
        $filter = $datagrid->getFilter('tags_adherents');
        $datagrid->setValue($filter->getName(), null, [TagEnum::ADHERENT]);

        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = $datagrid->getQuery();
        $alias = $queryBuilder->getAllAliases()[0];

        foreach (explode(' ', $value) as $key => $v) {
            $conditions = (new Expr())->orX();
            foreach ($property as $prop) {
                $conditions->add($alias.'.'.$prop.' LIKE :search_'.$key);
            }
            $queryBuilder->andWhere($conditions);
            $queryBuilder->setParameter('search_'.$key, '%'.$v.'%');
        }
    }

    public function getParent(): string
    {
        return ModelAutocompleteType::class;
    }
}
