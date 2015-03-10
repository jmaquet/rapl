<?php

namespace RAPL\Tests\Unit;

use RAPL\RAPL\Mapping\Route;
use RAPL\RAPL\UriBuilder;

class UriBuilderTest extends \PHPUnit_Framework_TestCase
{
    public function testCreateUri()
    {
        $classMetadata = \Mockery::mock('RAPL\RAPL\Mapping\ClassMetadata');

        $resource   = new Route('books/{id}.json');
        $collection = new Route('books.json');

        $classMetadata->shouldReceive('hasRoute')->withArgs(array('resource'))->andReturn(true)->atLeast(1);
        $classMetadata->shouldReceive('getRoute')->withArgs(array('resource'))->andReturn($resource)->once();
        $classMetadata->shouldReceive('getRoute')->withArgs(array('collection'))->andReturn($collection)->once();

        $uriBuilder = new UriBuilder($classMetadata);

        $criteria = array(
            'id' => 4
        );

        $actual = $uriBuilder->createUri($criteria);
        $this->assertSame('books/4.json', $actual);

        $criteria = array();

        $actual = $uriBuilder->createUri($criteria);
        $this->assertSame('books.json', $actual);
    }
}
