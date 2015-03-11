<?php

namespace RAPL\RAPL\Routing;

use RAPL\RAPL\Mapping\ClassMetadata;

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
}
