<?php

namespace RAPL\RAPL\Routing;

use RAPL\RAPL\Mapping\ClassMetadata;

interface RouterInterface
{
    /**
     * @param ClassMetadata $classMetadata
     * @param array         $criteria
     *
     * @return string
     */
    public function generate(ClassMetadata $classMetadata, array $criteria);
}
