<?php

namespace RAPL\RAPL\Routing;

use RAPL\RAPL\Mapping\ClassMetadata;
use RAPL\RAPL\Mapping\Route;

class Router implements RouterInterface
{
    /**
     * @param ClassMetadata $classMetadata
     * @param array         $criteria
     *
     * @return string
     */
    public function generate(ClassMetadata $classMetadata, array $criteria)
    {
        if ($classMetadata->hasRoute('resource') && count($criteria) === 1 && array_key_exists('id', $criteria)) {
            $route = $classMetadata->getRoute('resource');
        } else {
            $route = $classMetadata->getRoute('collection');
        }

        return $this->parseUrl($route, $criteria);
    }

    /**
     * @param Route $route
     * @param array $parameters
     *
     * @return string
     */
    protected function parseUrl(Route $route, array $parameters)
    {
        $uri = $route->getPattern();

        foreach ($parameters as $parameter => $value) {
            $count = 0;
            $uri   = str_replace(sprintf('{%s}', $parameter), $value, $uri, $count);

            if ($count > 0) {
                unset($parameters[$parameter]);
            }
        }

        if (count($parameters) > 0) {
            $uri .= '?' . $this->buildQueryString($parameters);
        }

        return $uri;
    }

    /**
     * @param array $parameters
     *
     * @return string
     */
    protected function buildQueryString(array $parameters)
    {
        return http_build_query($parameters);
    }
}
