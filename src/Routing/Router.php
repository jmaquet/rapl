<?php

namespace RAPL\RAPL\Routing;

class Router extends AbstractRouter
{
    /**
     * @param Query $query
     *
     * @return string
     */
    protected function buildQueryString(Query $query)
    {
        return http_build_query($query->getConditions());
    }
}
