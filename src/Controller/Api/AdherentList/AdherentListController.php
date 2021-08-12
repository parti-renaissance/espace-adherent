<?php

namespace App\Controller\Api\AdherentList;

use App\ManagedUsers\ManagedUsersFilter;
use App\ManagedUsers\ManagedUsersFilterFactory;
use App\Normalizer\ManagedUserNormalizer;
use App\Repository\Projection\ManagedUserRepository;
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
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

/**
 * @Route("/v3/adherents", name="app_adherents_list_get", methods={"GET"})
 *
 * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
 */
class AdherentListController extends AbstractController
{
    private $authorizationChecker;
    private $filterFactory;
    private $repository;
    private $denormalizer;

    public function __construct(
        AuthorizationChecker $authorizationChecker,
        ManagedUsersFilterFactory $filterFactory,
        ManagedUserRepository $repository,
        DenormalizerInterface $denormalizer
    ) {
        $this->authorizationChecker = $authorizationChecker;
        $this->filterFactory = $filterFactory;
        $this->repository = $repository;
        $this->denormalizer = $denormalizer;
    }

    public function __invoke(Request $request, UserInterface $user): Response
    {
        try {
            $this->authorizationChecker->isFeatureGranted($request, $user, FeatureEnum::CONTACTS);
        } catch (InvalidScopeException | ScopeQueryParamMissingException $e) {
            throw new BadRequestHttpException();
        } catch (ScopeExceptionInterface $e) {
            throw $this->createAccessDeniedException();
        }

        $scopeCode = $this->authorizationChecker->getScope($request);
        $filter = $this->filterFactory->createForScope($scopeCode, $user);

        $this->denormalizer->denormalize($request->query->all(), ManagedUsersFilter::class, null, [
            AbstractNormalizer::OBJECT_TO_POPULATE => $filter,
            AbstractNormalizer::GROUPS => ['filter_write'],
            AbstractObjectNormalizer::DISABLE_TYPE_ENFORCEMENT => true,
        ]);

        $adherents = $this->repository->searchByFilter($filter, $request->query->getInt('page', 1));

        return $this->json($adherents, Response::HTTP_OK, [], ['groups' => 'managed_user_read', ManagedUserNormalizer::FILTER_PARAM => $filter]);
    }
}
