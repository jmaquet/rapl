<?php

namespace RAPL\Tests\Unit\Routing;

use RAPL\RAPL\Mapping\Route;
use RAPL\RAPL\Routing\Router;

class RouterTest extends \PHPUnit_Framework_TestCase
{
    public function testGenerateUrl()
    {
        $resourceRoute   = new Route('books/{id}');
        $collectionRoute = new Route('books');

        $classMetadataMock = \Mockery::mock('RAPL\RAPL\Mapping\ClassMetadata');
        $classMetadataMock->shouldReceive('hasRoute')->withArgs(array('resource'))->andReturn(true);
        $classMetadataMock->shouldReceive('getRoute')->withArgs(array('resource'))->andReturn($resourceRoute);
        $classMetadataMock->shouldReceive('getRoute')->withArgs(array('collection'))->andReturn($collectionRoute);

        $router = new Router();

        $actual = $router->generate($classMetadataMock, array());
        $this->assertSame('books', $actual);

        $actual = $router->generate($classMetadataMock, array('id' => 3));
        $this->assertSame('books/3', $actual);

        $actual = $router->generate($classMetadataMock, array('title' => 'Foo'));
        $this->assertSame('books?title=Foo', $actual);
    }
}
