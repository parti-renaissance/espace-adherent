<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectingController extends Controller
{
    /**
     * @Route("/{url}", name="remove_trailing_slash", requirements={"url" = ".*\/$"}, methods={"GET"})
     */
    public function removeTrailingSlashAction(Request $request): Response
    {
        $pathInfo = $request->getPathInfo();
        $requestUri = $request->getRequestUri();

        $url = str_replace($pathInfo, rtrim($pathInfo, ' /'), $requestUri);

        return $this->redirect($url, Response::HTTP_MOVED_PERMANENTLY);
    }

    public function urlRedirectAction(Request $request, $path): Response
    {
        if (empty($path)) {
            throw $this->createNotFoundException();
        }

        if (parse_url($path, PHP_URL_SCHEME)) {
            $newRequest = Request::create($path, Request::METHOD_GET, $request->query->all());

            return $this->redirect(
                $newRequest->getSchemeAndHttpHost().$newRequest->getRequestUri().$newRequest->getBaseUrl()
            );
        }

        return $this->forward('FrameworkBundle:Redirect:urlRedirect', ['path' => $path]);
    }
}
