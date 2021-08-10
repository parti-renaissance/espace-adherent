<?php

namespace App\Controller\Api\Audience;

use App\Audience\AudienceHelper;
use App\Controller\EnMarche\AccessDelegatorTrait;
use App\Geo\ManagedZoneProvider;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class RetrieveAudiencesController extends AbstractController
{
    use AccessDelegatorTrait;

    public function __invoke(
        Request $request,
        EntityManagerInterface $entityManager,
        ManagedZoneProvider $managedZoneProvider
    ): array {
        if (!$type = $request->query->get('type')) {
            throw new BadRequestHttpException('No type provided.');
        }

        $className = AudienceHelper::getAudienceClassName($type);
        $user = $this->getMainUser($request->getSession());

        if (!$user || !AudienceHelper::validateAdherentAccess($user, $className)) {
            throw new AccessDeniedHttpException();
        }

        return $entityManager->getRepository($className)->findByZones(
            $managedZoneProvider->getManagedZones($user, AudienceHelper::getSpaceType($className))
        );
    }
}
