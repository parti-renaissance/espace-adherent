<?php

declare(strict_types=1);

namespace App\Security;

use App\Entity\Administrator;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Exception\MissingMandatoryParametersException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Authorization\AccessDeniedHandlerInterface;
use Symfony\Component\Security\Http\Firewall\SwitchUserListener;

class AccessDeniedHandler implements AccessDeniedHandlerInterface
{
    public function __construct(
        private readonly Security $security,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly string $adminRenaissanceHost,
    ) {
    }

    public function handle(Request $request, AccessDeniedException $accessDeniedException): ?Response
    {
        if ($request->isXmlHttpRequest() || \in_array('application/json', $request->getAcceptableContentTypes())) {
            return null;
        }

        $user = $this->security->getUser();

        if ($user instanceof Administrator && $request->getHost() !== $this->adminRenaissanceHost) {
            return new RedirectResponse($this->urlGenerator->generate('admin_app_adherent_list'));
        }

        if ($this->security->isGranted('IS_IMPERSONATOR')) {
            try {
                return new RedirectResponse(
                    $this->urlGenerator->generate($request->attributes->get('_route'), ['_switch_user' => SwitchUserListener::EXIT_VALUE])
                );
            } catch (MissingMandatoryParametersException $exception) {
            }
        }

        return null;
    }
}
