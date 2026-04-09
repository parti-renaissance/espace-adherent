<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\PushNotification;
use Doctrine\ORM\EntityManagerInterface;
use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class AdminPushNotificationController extends CRUDController
{
    public function autocompleteAction(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $search = trim((string) $request->query->get('q'));
        $page = max(1, $request->query->getInt('_page', 1));
        $perPage = max(1, $request->query->getInt('_per_page', 10));

        $qb = $em->createQueryBuilder()
            ->select('pn')
            ->from(PushNotification::class, 'pn')
            ->orderBy('pn.createdAt', 'DESC')
            ->setFirstResult(($page - 1) * $perPage)
            ->setMaxResults($perPage)
        ;

        if ('' !== $search) {
            $qb
                ->andWhere('pn.title LIKE :search')
                ->setParameter('search', '%'.$search.'%')
            ;
        }

        $total = (int) (clone $qb)->select('COUNT(pn.id)')->setFirstResult(0)->setMaxResults(null)->getQuery()->getSingleScalarResult();

        $items = array_map(
            fn (PushNotification $pn) => ['id' => $pn->getId(), 'label' => (string) $pn],
            $qb->getQuery()->getResult(),
        );

        return new JsonResponse([
            'items' => $items,
            'more' => ($page * $perPage) < $total,
            'status' => 'OK',
        ]);
    }
}
