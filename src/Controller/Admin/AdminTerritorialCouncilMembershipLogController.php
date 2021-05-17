<?php

namespace App\Controller\Admin;

use App\Entity\TerritorialCouncil\TerritorialCouncilMembershipLog;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Route("/territorial-council-membership-log")
 */
class AdminTerritorialCouncilMembershipLogController extends AbstractController
{
    public const STATUS_RESOLVED = 'resolved';
    public const STATUS_UNRESOLVED = 'unresolved';
    public const STATUS = [
        self::STATUS_RESOLVED,
        self::STATUS_UNRESOLVED,
    ];

    /**
     * @Route("/{membershipLog}/{status}", name="app_admin_territorial_council_membership_log_resolve", methods={"GET"})
     * @Security("has_role('ROLE_ADMIN_TERRITORIAL_COUNCIL_MEMBERSHIP_LOG')")
     */
    public function changeResolvedAction(
        Request $request,
        TerritorialCouncilMembershipLog $membershipLog,
        string $status,
        TranslatorInterface $translator,
        EntityManagerInterface $entityManager
    ): Response {
        if (!\in_array($status, self::STATUS)) {
            throw new BadRequestHttpException(sprintf('Status "%s" is not authorized.', $status));
        }

        if (self::STATUS_RESOLVED === $status && $membershipLog->isResolved()) {
            throw new BadRequestHttpException($translator->trans('territorial_council_membership_log.is_resolved'));
        }

        if (self::STATUS_UNRESOLVED === $status && !$membershipLog->isResolved()) {
            throw new BadRequestHttpException($translator->trans('territorial_council_membership_log.is_not_resolved'));
        }

        if (!$this->isCsrfTokenValid(sprintf('territorial_council_membership_log.resolve.%s', $membershipLog->getId()), $request->query->get('token'))) {
            throw new BadRequestHttpException('Invalid Csrf token provided.');
        }

        $membershipLog->setIsResolved(self::STATUS_RESOLVED === $status);

        $entityManager->flush();

        return $this->redirectToRoute('admin_app_territorialcouncil_territorialcouncilmembershiplog_list', [
            'id' => $membershipLog->getId(),
        ]);
    }
}
