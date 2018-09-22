<?php

namespace AppBundle\React;

use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class ReactAppRegistry
{
    /**
     * @var Route[][]
     */
    private $apps;

    public function __construct()
    {
        $this->apps = [
            'projets-citoyens' => [
                'home' => new Route('/projets-citoyens'),
                'discover' => new Route('/projets-citoyens/decouvrir'),
                'search' => new Route('/projets-citoyens/recherche'),
            ],
        ];
    }

    public function readManifest(string $app): ?array
    {
        if (!isset($this->apps[$app])) {
            return null;
        }

        try {
            $data = \GuzzleHttp\json_decode(file_get_contents(__DIR__.'/../../web/apps/'.$app.'/build/asset-manifest.json'), true);
        } catch (\Exception $e) {
            return null;
        }

        $manifest = ['css' => [], 'js' => []];

        foreach ($data as $file) {
            if ('.css' === substr($file, -4)) {
                $manifest['css'][] = 'apps/'.$app.'/build/'.$file;
            } elseif ('.js' === substr($file, -3)) {
                $manifest['js'][] = 'apps/'.$app.'/build/'.$file;
            }
        }

        return $manifest;
    }

    public function loadRoutes(): RouteCollection
    {
        $collection = new RouteCollection();

        foreach ($this->apps as $app => $routes) {
            foreach ($routes as $name => $route) {
                $route->setDefault('_react_app', $app);
                $route->setDefault('_controller', 'AppBundle:React:app');

                $collection->add('react_app_'.$app.'_'.$name, $route);
            }
        }

        return $collection;
    }
}
