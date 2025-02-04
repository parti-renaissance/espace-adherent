<?php

namespace App\Command;

use App\Entity\Action\Action;
use App\Entity\Adherent;
use App\Entity\AuthorInstanceInterface;
use App\Entity\EntityScopeVisibilityWithZoneInterface;
use App\Entity\Event\Event;
use App\Entity\Jecoute\News;
use App\Entity\MyTeam\DelegatedAccess;
use App\MyTeam\RoleEnum;
use App\Repository\MyTeam\DelegatedAccessRepository;
use App\Scope\Generator\ScopeGeneratorInterface;
use App\Scope\ScopeEnum;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:author:update-scope',
)]
class UpdateAuthorScopeCommand extends Command
{
    private const AUTHOR_INSTANCE_ENTITIES = [
        Action::class,
        Event::class,
        News::class,
    ];

    /** @var SymfonyStyle */
    private $io;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly DelegatedAccessRepository $delegatedAccessRepository,
    ) {
        parent::__construct();
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        foreach (self::AUTHOR_INSTANCE_ENTITIES as $entityClass) {
            $this->io->section("Updating $entityClass author scopes");

            $paginator = $this->getPaginator($entityClass);
            $paginator->getQuery()->setMaxResults(500);

            $count = $paginator->count();

            $this->io->progressStart($count);
            $offset = 0;

            do {
                foreach ($paginator as $entity) {
                    ++$offset;
                    $this->io->progressAdvance();

                    if ($entity->getAuthorScope()) {
                        continue;
                    }

                    if (!$roleLabel = $entity->getAuthorRole()) {
                        continue;
                    }

                    $roleScope = array_search($roleLabel, ScopeEnum::ROLE_NAMES, true);
                    $delegatedRoleScope = array_search($roleLabel, RoleEnum::LABELS, true);

                    if (false !== $roleScope) {
                        $this->updateAuthorScope($entity, $roleScope);
                    } elseif (false !== $delegatedRoleScope) {
                        try {
                            $delegatedAccess = $this->findDelegatedAccessForRole(
                                $entity->getAuthor(),
                                $delegatedRoleScope
                            );
                        } catch (NonUniqueResultException $e) {
                            continue;
                        }

                        if ($delegatedAccess) {
                            $this->updateAuthorScope(
                                $entity,
                                ScopeGeneratorInterface::DELEGATED_SCOPE_PREFIX.$delegatedAccess->getUuid()->toString()
                            );
                        }
                    }
                }

                $paginator->getQuery()->setFirstResult($offset);

                $this->entityManager->clear();
            } while ($offset < $count);

            $this->io->progressFinish();
        }

        return self::SUCCESS;
    }

    private function updateAuthorScope(AuthorInstanceInterface $entity, string $authorScope): void
    {
        $entity->setAuthorScope($authorScope);

        $this->entityManager->flush();
    }

    /** @return AuthorInstanceInterface[]|EntityScopeVisibilityWithZoneInterface[]|Paginator */
    private function getPaginator(string $entityClass): Paginator
    {
        if (!is_a($entityClass, AuthorInstanceInterface::class, true)) {
            throw new \InvalidArgumentException(\sprintf('Class "%s" does not implement "%s".', $entityClass, AuthorInstanceInterface::class));
        }

        return new Paginator(
            $this
                ->entityManager
                ->getRepository($entityClass)
                ->createQueryBuilder('e')
                ->select('e')
                ->getQuery()
        );
    }

    /** @throws NonUniqueResultException */
    private function findDelegatedAccessForRole(
        Adherent $adherent,
        string $role,
    ): ?DelegatedAccess {
        return $this
            ->delegatedAccessRepository
            ->createQueryBuilder('da')
            ->select('da')
            ->andWhere('da.delegated = :delegated')
            ->andWhere('da.role = :role')
            ->setParameter('delegated', $adherent)
            ->setParameter('role', $role)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
