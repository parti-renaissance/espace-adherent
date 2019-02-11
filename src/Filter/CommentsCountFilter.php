<?php

namespace AppBundle\Filter;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\AbstractFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use AppBundle\Entity\IdeasWorkshop\Idea;
use Doctrine\ORM\QueryBuilder;

final class CommentsCountFilter extends AbstractFilter
{
    protected function filterProperty(
        string $property, $value,
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        string $operationName = null
    ): void {
        if (Idea::class !== $resourceClass
            || !array_key_exists($property, $this->properties)
            || !\in_array($value = strtolower($value), ['desc', 'asc'])
        ) {
            return;
        }

        $alias = $queryBuilder->getRootAliases()[0];

        $queryBuilder
            ->addSelect('COUNT(threadComment) as HIDDEN threadCommentCount')
            ->leftJoin($alias.'.answers', 'answer')
            ->leftJoin('answer.threads', 'thread')
            ->leftJoin('thread.comments', 'threadComment')
            ->groupBy($alias.'.id')
            ->addOrderBy('threadCommentCount', $value)
        ;
    }

    public function getDescription(string $resourceClass): array
    {
        if (!$this->properties) {
            return [];
        }

        $description = [];
        foreach ($this->properties as $property => $strategy) {
            $description['commentsCount'] = [
                'property' => $property,
                'type' => 'string',
                'required' => false,
                'swagger' => [
                    'description' => 'Order by comments count.',
                    'name' => 'commentsCount',
                    'type' => 'string',
                ],
            ];
        }

        return $description;
    }
}
