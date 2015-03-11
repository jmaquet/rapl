<?php

namespace RAPL\RAPL\Routing;

class Query
{
    /**
     * @var array
     */
    protected $conditions = array();

    /**
     * @var array
     */
    protected $orderBy = array();

    /**
     * @var int|null
     */
    protected $limit;

    /**
     * @var int|null
     */
    protected $offset;

    /**
     * @param array    $conditions
     * @param array    $orderBy
     * @param int|null $limit
     * @param int|null $offset
     */
    public function __construct(array $conditions = array(), array $orderBy = array(), $limit = null, $offset = null)
    {
        $this->conditions = $conditions;
        $this->orderBy    = $orderBy;
        $this->limit      = $limit;
        $this->offset     = $offset;
    }

    /**
     * @return array
     */
    public function getConditions()
    {
        return $this->conditions;
    }

    /**
     * @param $property
     */
    public function removeCondition($property)
    {
        unset ($this->conditions[$property]);
    }

    /**
     * @return array
     */
    public function getOrderBy()
    {
        return $this->orderBy;
    }

    /**
     * @return int|null
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * @return int|null
     */
    public function getOffset()
    {
        return $this->offset;
    }
}
