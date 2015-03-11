<?php

namespace RAPL\RAPL\Routing;

class Router extends AbstractRouter
{
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
