<?php

namespace RAPL\Tests\Unit\Serializer;

use RAPL\RAPL\Mapping\ClassMetadata;
use RAPL\RAPL\Serializer\Serializer;

class SerializerTest extends \PHPUnit_Framework_TestCase
{
    public function testDeserialize()
    {
        $className         = 'Foo\Bar';
        $embeddedClassName = 'Foo\BarBaz';

        $classMetadata = $this->getClassMetadata($className);

        $expectedEmbeddedData = array(
            'foo' => 'Bar'
        );

        $returnedEmbeddedEntity = \Mockery::mock($embeddedClassName);

        $expectedData = array(
            'stringProperty'      => 'Foo Bar',
            'integerProperty'     => 5,
            'booleanProperty'     => false,
            'datetimeProperty'    => new \DateTime('2014-12-10 14:32:01'),
            'embedOneAssociation' => $returnedEmbeddedEntity,
        );

        $returnedEntity = \Mockery::mock($className);

        $unitOfWork = \Mockery::mock('RAPL\RAPL\UnitOfWork');
        $unitOfWork->shouldReceive('createEntity')->withArgs(array('Foo\BarBaz', $expectedEmbeddedData))->andReturn(
            $returnedEmbeddedEntity
        )->once();
        $unitOfWork->shouldReceive('createEntity')->withArgs(array($className, $expectedData))->andReturn(
            $returnedEntity
        )->once();

        $embeddedClassMetadata = $this->getEmbeddedClassMetadata();
        $classMetadataFactory  = $this->mockClassMetadataFactory(array('Foo\BarBaz' => $embeddedClassMetadata));

        $serializer = new Serializer($classMetadata, $unitOfWork, $classMetadataFactory);

        $classMetadata->shouldReceive('getFieldName')->withArgs(array('unknown'))->andReturn('unknown')->once();
        $classMetadata->shouldReceive('hasField')->withArgs(array('unknown'))->andReturn(false)->once();

        $data = '{"results":[{
            "string": "Foo Bar",
            "integer": 5,
            "boolean": false,
            "datetime": "2014-12-10 14:32:01",
            "embedOne": {
                "foo": "Bar"
            },
            "unknown": "adsf"
        }]}';

        $actual = $serializer->deserialize($data, true, array('results'));

        $this->assertSame(1, count($actual));
        $this->assertSame($returnedEntity, $actual[0]);
    }

    public function testDeserializeWithNullAssociation()
    {
        $className = 'Foo\Bar';

        $classMetadata         = $this->mockClassMetadata(
            'Foo\Bar',
            array(
                'embedOneAssociation' => array(
                    'serializedName' => 'embedOne',
                    'embedded'       => true,
                    'type'           => 'one',
                    'association'    => ClassMetadata::EMBED_ONE,
                    'targetEntity'   => 'Foo\BarBaz'
                )
            )
        );
        $embeddedClassMetadata = $this->mockClassMetadata(
            'Foo\BarBaz',
            array(
                'foo' => array(
                    'serializedName' => 'foo',
                    'type'           => 'string'
                )
            )
        );

        $classMetadataFactory = $this->mockClassMetadataFactory(array('Foo\BarBaz' => $embeddedClassMetadata));

        $expectedData = array(
            'embedOneAssociation' => null,
        );

        $returnedEntity = \Mockery::mock($className);

        $unitOfWork = \Mockery::mock('RAPL\RAPL\UnitOfWork');
        $unitOfWork->shouldReceive('createEntity')->withArgs(array($className, $expectedData))->andReturn(
            $returnedEntity
        )->once();

        $serializer = new Serializer($classMetadata, $unitOfWork, $classMetadataFactory);

        $data = '{"results":[{
            "embedOne": null
        }]}';

        $actual = $serializer->deserialize($data, true, array('results'));

        $this->assertSame(1, count($actual));
        $this->assertSame($returnedEntity, $actual[0]);
    }

    public function testDeserializeUnknownDataType()
    {
        $className = 'Foo\Bar';

        $classMetadata = $this->mockClassMetadata(
            'Foo\Bar',
            array(
                'foo' => array(
                    'serializedName' => 'foo',
                    'type'           => 'foo'
                )
            )
        );

        $classMetadataFactory = $this->mockClassMetadataFactory();

        $expectedData = array(
            'foo' => null,
        );

        $returnedEntity = \Mockery::mock($className);

        $unitOfWork = \Mockery::mock('RAPL\RAPL\UnitOfWork');
        $unitOfWork->shouldReceive('createEntity')->withArgs(array($className, $expectedData))->andReturn(
            $returnedEntity
        )->once();

        $serializer = new Serializer($classMetadata, $unitOfWork, $classMetadataFactory);

        $data = '{"results":[{
            "foo": "barfoo"
        }]}';

        $actual = $serializer->deserialize($data, true, array('results'));

        $this->assertSame(1, count($actual));
        $this->assertSame($returnedEntity, $actual[0]);
    }

    /**
     * @param array $classMetadata
     *
     * @return \Mockery\MockInterface|\RAPL\RAPL\Mapping\ClassMetadataFactory
     */
    protected function mockClassMetadataFactory(array $classMetadata = array())
    {
        $classMetadataFactory = \Mockery::mock('RAPL\RAPL\Mapping\ClassMetadataFactory');
        foreach ($classMetadata as $className => $metadata) {
            $classMetadataFactory->shouldReceive('getMetadataFor')->withArgs(array($className))->andReturn(
                $metadata
            )->once();
        }

        return $classMetadataFactory;
    }

    /**
     * @param string $className
     * @param array  $propertyMappings
     *
     * @return \Mockery\MockInterface|\RAPL\RAPL\Mapping\ClassMetadata
     */
    protected function mockClassMetadata($className, array $propertyMappings)
    {
        $classMetadata = \Mockery::mock('RAPL\RAPL\Mapping\ClassMetadata');
        $classMetadata->shouldReceive('getName')->andReturn($className);
        $classMetadata->shouldReceive('getFormat')->andReturn('json');

        foreach ($propertyMappings as $name => $mapping) {
            $classMetadata->shouldReceive('getFieldName')->with($mapping['serializedName'])->andReturn($name);
            $classMetadata->shouldReceive('hasField')->with($name)->andReturn(true);
            $classMetadata->shouldReceive('getFieldMapping')->with($name)->andReturn($mapping);
        }

        return $classMetadata;
    }

    /**
     * @return \Mockery\MockInterface|ClassMetadata
     */
    protected function getEmbeddedClassMetadata()
    {
        $propertyMappings = array(
            'foo' => array(
                'serializedName' => 'foo',
                'type'           => 'string'
            )
        );

        return $this->mockClassMetadata('Foo\BarBaz', $propertyMappings);
    }

    /**
     * @param $className
     *
     * @return \Mockery\MockInterface|\RAPL\RAPL\Mapping\ClassMetadata
     */
    protected function getClassMetadata($className)
    {
        $propertyMappings = array(
            'stringProperty'      => array(
                'serializedName' => 'string',
                'type'           => 'string'
            ),
            'integerProperty'     => array(
                'serializedName' => 'integer',
                'type'           => 'integer'
            ),
            'booleanProperty'     => array(
                'serializedName' => 'boolean',
                'type'           => 'boolean'
            ),
            'datetimeProperty'    => array(
                'serializedName' => 'datetime',
                'type'           => 'datetime',
            ),
            'embedOneAssociation' => array(
                'serializedName' => 'embedOne',
                'embedded'       => true,
                'type'           => 'one',
                'association'    => ClassMetadata::EMBED_ONE,
                'targetEntity'   => 'Foo\BarBaz'
            )
        );

        return $this->mockClassMetadata($className, $propertyMappings);
    }
}
