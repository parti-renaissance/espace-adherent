<?php

namespace App\Controller\Api\AdherentList;

use App\Entity\Adherent;
use App\ManagedUsers\ColumnsConfigurator;
use App\Scope\AuthorizationChecker;
use App\Scope\Exception\InvalidScopeException;
use App\Scope\Exception\ScopeExceptionInterface;
use App\Scope\Exception\ScopeQueryParamMissingException;
use App\Scope\FeatureEnum;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @IsGranted("IS_AUTHENTICATED_FULLY")
 */
#[Route(path: '/v3/adherents/columns', name: 'app_adherents_list_get_columns', methods: ['GET'])]
class ColumnsConfigurationController extends AbstractController
{
    private $authorizationChecker;
    private $columnsConfigurator;

    public function __construct(AuthorizationChecker $authorizationChecker, ColumnsConfigurator $columnsConfigurator)
    {
        $this->authorizationChecker = $authorizationChecker;
        $this->columnsConfigurator = $columnsConfigurator;
    }

    public function __invoke(Request $request): Response
    {
        /** @var Adherent $user */
        $user = $this->getUser();
        try {
            $this->authorizationChecker->isFeatureGranted($request, $user, [FeatureEnum::CONTACTS]);
        } catch (InvalidScopeException|ScopeQueryParamMissingException $e) {
            throw new BadRequestHttpException();
        } catch (ScopeExceptionInterface $e) {
            throw $this->createAccessDeniedException();
        }

        return $this->json($this->columnsConfigurator->getConfig());
    }
}
