<?php

namespace App\Controller\Api\AdherentFilter;

use App\AdherentFilter\AdherentFiltersGenerator;
use App\Scope\AuthorizationChecker;
use App\Scope\Exception\InvalidScopeException;
use App\Scope\Exception\ScopeExceptionInterface;
use App\Scope\Exception\ScopeQueryParamMissingException;
use App\Scope\FeatureEnum;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @Route("/v3/adherents/filters", name="app_adherents_filters_get", methods={"GET"})
 *
 * @Security("is_granted('IS_AUTHENTICATED_FULLY') and is_granted('REQUEST_SCOPE_GRANTED')")
 */
class GetFilterCollectionController extends AbstractController
{
    private AuthorizationChecker $authorizationChecker;
    private AdherentFiltersGenerator $builder;

    public function __construct(AuthorizationChecker $authorizationChecker, AdherentFiltersGenerator $builder)
    {
        $this->authorizationChecker = $authorizationChecker;
        $this->builder = $builder;
    }

    public function __invoke(Request $request, UserInterface $user): Response
    {
        try {
            $this->authorizationChecker->isFeatureGranted($request, $user, FeatureEnum::CONTACTS);
        } catch (InvalidScopeException|ScopeQueryParamMissingException $e) {
            throw new BadRequestHttpException();
        } catch (ScopeExceptionInterface $e) {
            throw $this->createAccessDeniedException();
        }

        return $this->json(
            $this->builder->generate($this->authorizationChecker->getScopeGenerator($request, $user)->getCode(), $request->query->get('feature')),
            Response::HTTP_OK,
            [],
            ['groups' => ['filter:read']]
        );
    }
}
