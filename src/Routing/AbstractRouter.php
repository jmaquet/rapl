<?php

namespace RAPL\RAPL\Routing;

use RAPL\RAPL\Mapping\ClassMetadata;
use RAPL\RAPL\Mapping\MappingException;
use RAPL\RAPL\Mapping\Route;

abstract class AbstractRouter implements RouterInterface
{
    /**
     * @param ClassMetadata $classMetadata
     * @param array         $criteria
     *
     * @return string
     */
    public function generate(ClassMetadata $classMetadata, array $criteria)
    {
        $route = $this->selectRoute($classMetadata, $criteria);

        $path        = $this->buildPath($route->getPattern(), $criteria);
        $queryString = $this->buildQueryString($criteria);

        if (empty($queryString)) {
            return $path;
        } else {
            return $path . '?' . $queryString;
        }
    }

    /**
     * @param ClassMetadata $classMetadata
     * @param array         $criteria
     *
     * @return Route
     *
     * @throws MappingException
     */
    protected function selectRoute(ClassMetadata $classMetadata, array $criteria)
    {
        if ($classMetadata->hasRoute('resource') && count($criteria) === 1 && array_key_exists('id', $criteria)) {
            return $classMetadata->getRoute('resource');
        } elseif ($classMetadata->hasRoute('collection')) {
            return $classMetadata->getRoute('collection');
        }

        throw MappingException::routeNotConfigured($classMetadata->getName(), 'collection');
    }

    /**
     * @param string $pattern
     * @param array  $conditions
     *
     * @return string
     */
    protected function buildPath($pattern, array &$conditions)
    {
        $path = $pattern;

        foreach ($conditions as $parameter => $value) {
            $count = 0;
            $path  = str_replace(sprintf('{%s}', $parameter), $value, $path, $count);

            if ($count > 0) {
                unset($conditions[$parameter]);
            }
        }

        return $path;
    }

    /**
     * @param array $conditions
     *
     * @return string
     */
    abstract protected function buildQueryString(array $conditions);
}
