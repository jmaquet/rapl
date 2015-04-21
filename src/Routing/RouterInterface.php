<?php

namespace RAPL\RAPL\Routing;

use RAPL\RAPL\Mapping\ClassMetadata;
use RAPL\RAPL\Mapping\Route;

interface RouterInterface
{
    /**
     * @param ClassMetadata $classMetadata
     * @param array         $conditions
     * @param array         $orderBy
     * @param int|null      $limit
     * @param int|null      $offset
     *
     * @return string
     */
    public function generate(ClassMetadata $classMetadata, array $conditions, array $orderBy, $limit, $offset);

    /**
     * @param ClassMetadata $classMetadata
     * @param string         $query
     * @param array         $conditions
     * @param array         $orderBy
     * @param int|null      $limit
     * @param int|null      $offset
     *
     * @return string
     */
    public function generateSpecific(ClassMetadata $classMetadata, $query, array $conditions, array $orderBy, $limit, $offset);
    //NEW

    /**
     * @param ClassMetadata $classMetadata
     * @param array         $conditions
     * @param array         $orderBy
     * @param int|null      $limit
     * @param int|null      $offset
     *
     * @return Route
     */
    public function getRoute(ClassMetadata $classMetadata, array $conditions, array $orderBy, $limit, $offset);

    /**
     * Asking for route registered in configuration file for an action
     *
     * @param ClassMetadata $classMetadata
     * @param string         $type
     *
     * @return Route
     * @throws MappingException
     */
    public function chooseRoute(ClassMetadata $classMetadata, $type);
    //NEW
}
