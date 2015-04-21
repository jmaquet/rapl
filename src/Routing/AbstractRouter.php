<?php

namespace RAPL\RAPL\Routing;

use RAPL\RAPL\Mapping\ClassMetadata;
use RAPL\RAPL\Mapping\MappingException;
use RAPL\RAPL\Mapping\Route;

abstract class AbstractRouter implements RouterInterface
{
    /**
     * @param ClassMetadata $classMetadata
     * @param array         $conditions
     * @param array         $orderBy
     * @param int|null      $limit
     * @param int|null      $offset
     *
     * @return string
     * @throws MappingException
     */
    public function generate(
        ClassMetadata $classMetadata,
        array $conditions = array(),
        array $orderBy = array(),
        $limit = null,
        $offset = null
    ) {
        $query = new Query($conditions, $orderBy, $limit, $offset);

        $route = $this->selectRoute($classMetadata, $query);

        $path = $this->buildPath($route->getPattern(), $query);
        $queryString = $this->buildQueryString($query);

        if (empty($queryString)) {
            return $path;
        } else {
            return $path . '?' . $queryString;
        }
    }

    /**
     * @param ClassMetadata $classMetadata
     * @param string         $type
     * @param array         $conditions
     * @param array         $orderBy
     * @param int|null      $limit
     * @param int|null      $offset
     *
     * @return string
     * @throws MappingException
     */
    public function generateSpecific(
            ClassMetadata $classMetadata,
            $type,
            array $conditions = array(),
            array $orderBy = array(),
            $limit = null,
            $offset = null
    ) {
        $query = new Query($conditions, $orderBy, $limit, $offset);

        $route = $this->chooseRoute($classMetadata, $type);

        $path = $this->buildPath($route->getPattern(), $query);
        $queryString = $this->buildQueryString($query);

        if (empty($queryString)) {
            return $path;
        } else {
            return $path . '?' . $queryString;
        }
    }

    /**
     * @param ClassMetadata $classMetadata
     * @param array         $conditions
     * @param array         $orderBy
     * @param int|null      $limit
     * @param int|null      $offset
     *
     * @return Route
     * @throws MappingException
     */
    public function getRoute(
        ClassMetadata $classMetadata,
        array $conditions = array(),
        array $orderBy = array(),
        $limit = null,
        $offset = null
    ) {
        $query = new Query($conditions, $orderBy, $limit, $offset);

        return $this->selectRoute($classMetadata, $query);
    }

    /**
     * @param ClassMetadata $classMetadata
     * @param Query         $query
     *
     * @return Route
     * @throws MappingException
     */
    protected function selectRoute(ClassMetadata $classMetadata, Query $query)
    {
        //NEW
        if ($classMetadata->hasRoute('resource') && count($query->getConditions()) > 0 && array_key_exists(
                'id',
                $query->getConditions()
            )
        ) {
            return $classMetadata->getRoute('resource');
        } elseif ($classMetadata->hasRoute('collection')) {
            return $classMetadata->getRoute('collection');
        } elseif ($classMetadata->hasRoute('creation')) {
            return $classMetadata->getRoute('creation');
        }

        throw MappingException::routeNotConfigured($classMetadata->getName(), 'collection');
    }

    /**
     * @param ClassMetadata $classMetadata
     * @param string        $type
     *
     * @return Route
     * @throws MappingException
     */
    public function chooseRoute(ClassMetadata $classMetadata, $type)
    {
        //NEW
        if ($classMetadata->hasRoute($type)) {
            return $classMetadata->getRoute($type);
        }

        throw MappingException::routeNotConfigured($classMetadata->getName(), $type);
    }

    /**
     * @param string $pattern
     * @param Query  $query
     *
     * @return string
     */
    protected function buildPath($pattern, Query $query)
    {
        $path = $pattern;

        foreach ($query->getConditions() as $parameter => $value) {
            $count = 0;
            $path  = str_replace(sprintf('{%s}', $parameter), $value, $path, $count);

            if ($count > 0) {
                $query->removeCondition($parameter);
            }
        }

        return $path;
    }

    /**
     * @param Query $query
     *
     * @return string
     */
    abstract protected function buildQueryString(Query $query);
}
