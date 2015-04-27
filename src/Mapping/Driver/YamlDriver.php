<?php

namespace RAPL\RAPL\Mapping\Driver;

use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\Common\Persistence\Mapping\Driver\FileDriver;
use Doctrine\Common\Persistence\Mapping\Driver\FileLocator;
use RAPL\RAPL\Mapping\Route;
use Symfony\Component\Yaml\Yaml;

class YamlDriver extends FileDriver
{
    const DEFAULT_FILE_EXTENSION = '.rapl.yml';

    /**
     * @param string|array|FileLocator $locator
     * @param string                   $fileExtension
     */
    public function __construct($locator, $fileExtension = self::DEFAULT_FILE_EXTENSION)
    {
        parent::__construct($locator, $fileExtension);
    }

    /**
     * Loads the metadata for the specified class into the provided container.
     *
     * @param string                                         $className
     * @param ClassMetadata|\RAPL\RAPL\Mapping\ClassMetadata $metadata
     *
     * @return void
     */
    public function loadMetadataForClass($className, ClassMetadata $metadata)
    {
        $element = $this->getElement($className);

        if (isset($element['format'])) {
            $metadata->setFormat($element['format']);
        }

        $this->setRoutes($metadata, $element);

        if (isset($element['identifiers'])) {
            foreach ($element['identifiers'] as $fieldName => $fieldElement) {
                $metadata->mapField(
                    array(
                        'fieldName'      => $fieldName,
                        'type'           => (isset($fieldElement['type'])) ? $fieldElement['type'] : null,
                        'serializedName' => (isset($fieldElement['serializedName'])) ? (string) $fieldElement['serializedName'] : null,
                        'id'             => true
                    )
                );
            }
        }

        if (isset($element['fields'])) {
            foreach ($element['fields'] as $fieldName => $mapping) {
                $metadata->mapField(
                    array(
                        'fieldName'      => $fieldName,
                        'type'           => (isset($mapping['type'])) ? $mapping['type'] : null,
                        'serializedName' => (isset($mapping['serializedName'])) ? (string) $mapping['serializedName'] : null,
                        'subfields' => (isset($mapping['subfields'])) ? $mapping['subfields'] : null
                    )
                );
            }
        }

        if (isset($element['embedOne'])) {
            foreach ($element['embedOne'] as $fieldName => $embedElement) {
                $metadata->mapEmbedOne(
                    array(
                        'targetEntity'   => (string) $embedElement['targetEntity'],
                        'fieldName'      => $fieldName,
                        'serializedName' => (isset($embedElement['serializedName'])) ? (string) $embedElement['serializedName'] : null
                    )
                );
            }
        }
    }

    /**
     * @param ClassMetadata|\RAPL\RAPL\Mapping\ClassMetadata $metadata
     * @param array                                          $element
     */
    protected function setRoutes(ClassMetadata $metadata, array $element)
    {
        //NEW
        foreach (array('resource', 'collection', 'creation', 'remove', 'update') as $type) {
            if (isset($element[$type]) && isset($element[$type]['route'])) {
                $pattern   = $element[$type]['route'];
                $envelopes = (isset($element[$type]['envelopes'])) ? $element[$type]['envelopes'] : array();

                if ($type === 'resource' || $type === 'creation' || $type === 'remove'  || $type === 'update') {
                    $returnsCollection = false;
                } else {
                    $returnsCollection = true;
                }

                $metadata->setRoute($type, new Route($pattern, $returnsCollection, $envelopes));
            }
        }
    }

    /**
     * Loads a mapping file with the given name and returns a map
     * from class/entity names to their corresponding file driver elements.
     *
     * @param string $file The mapping file to load.
     *
     * @return array
     */
    protected function loadMappingFile($file)
    {
        return Yaml::parse($file);
    }
}
