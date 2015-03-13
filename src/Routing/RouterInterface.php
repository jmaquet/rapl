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
     * @param array         $conditions
     * @param array         $orderBy
     * @param int|null      $limit
     * @param int|null      $offset
     *
     * @return Route
     */
    public function getRoute(ClassMetadata $classMetadata, array $conditions, array $orderBy, $limit, $offset);
}
