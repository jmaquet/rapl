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
     * @param array $conditions
     *
     * @return string
     */
    protected function parseUrl(Route $route, array $conditions)
    {
        $uri = $route->getPattern();

        foreach ($conditions as $parameter => $value) {
            $count = 0;
            $uri   = str_replace(sprintf('{%s}', $parameter), $value, $uri, $count);

            if ($count > 0) {
                unset($conditions[$parameter]);
            }
        }

        if (count($conditions) > 0) {
            $uri .= '?' . $this->buildQueryString($conditions);
        }

        return $uri;
    }

    /**
     * @param array $conditions
     *
     * @return string
     */
    protected function buildQueryString(array $conditions)
    {
        return http_build_query($conditions);
    }
}
