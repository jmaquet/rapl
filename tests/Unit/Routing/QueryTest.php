<?php

namespace RAPL\Tests\Unit\Routing;

use RAPL\RAPL\Routing\Query;

class QueryTest extends \PHPUnit_Framework_TestCase
{
    public function testQuery()
    {
        $conditions = array('foo' => 'bar');
        $orderBy    = array('foo' => 'desc');
        $limit      = 5;
        $offset     = 0;

        $query = new Query($conditions, $orderBy, $limit, $offset);

        $this->assertSame($conditions, $query->getConditions());
        $this->assertSame($orderBy, $query->getOrderBy());
        $this->assertSame($limit, $query->getLimit());
        $this->assertSame($offset, $query->getOffset());
    }

    public function testRemoveCondition()
    {
        $conditions = array('foo' => 'bar', 'bar' => 'barbaz');

        $query = new Query($conditions);

        $query->removeCondition('foo');
        $this->assertSame(array('bar' => 'barbaz'), $query->getConditions());
    }
}
