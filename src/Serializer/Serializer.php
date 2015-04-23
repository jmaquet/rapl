<?php

namespace RAPL\RAPL\Serializer;

use Danone\BoinsiderBundle\RAPL\Plugin\SerializerPlugin;
use Doctrine\Common\Persistence\Mapping\ClassMetadataFactory;
use RAPL\RAPL\Mapping\ClassMetadata;
use RAPL\RAPL\UnitOfWork;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\Serializer\Serializer as SymfonySerializer;

class Serializer implements SerializerInterface
{
    /**
     * @var ClassMetadata
     */
    private $classMetadata;

    /**
     * @var UnitOfWork
     */
    private $unitOfWork;

    /**
     * @var ClassMetadataFactory
     */
    private $classMetadataFactory;

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @var SerializerPlugin
     */
    private $serializedPlugin;

    /**
     * @param ClassMetadata        $metadata
     * @param UnitOfWork           $unitOfWork
     * @param ClassMetadataFactory $metadataFactory
     */
    public function __construct(ClassMetadata $metadata, UnitOfWork $unitOfWork, ClassMetadataFactory $metadataFactory, SerializerPlugin $serializerPlugin = null)
    {
        $this->classMetadata        = $metadata;
        $this->unitOfWork           = $unitOfWork;
        $this->classMetadataFactory = $metadataFactory;

        $normalizers      = array(new GetSetMethodNormalizer());
        $encoders         = array(new JsonEncoder());
        $this->serializer = new SymfonySerializer($normalizers, $encoders);

        $this->serializedPlugin = $serializerPlugin;
    }

    /**
     * Deserializes serialized data
     *
     * @param string  $data
     * @param boolean $isCollection
     * @param array   $envelopes
     *
     * @return array
     */
    public function deserialize($data, $isCollection, array $envelopes = array())
    {
        $data = $this->decode($data);
        $data = $this->unwrap($data, $envelopes);

        if (!$isCollection) {
            $entityData = $this->mapFromSerialized($data);
            $entityData = $this->hydrateSingleEntity($entityData);
            return $entityData;
        }

        $hydratedEntities = array();

        foreach ($data as $entityData) {
            $entityData = $this->mapFromSerialized($entityData);

            $hydratedEntities[] = $this->hydrateSingleEntity($entityData);
        }

        return $hydratedEntities;
    }

    /**
     * @param string $data
     *
     * @return array
     */
    private function decode($data)
    {
        return $this->serializer->decode($data, $this->classMetadata->getFormat());
    }

    /**
     * @param array $data
     * @param array $result
     */
    private function hydrateSingleEntity(array $data)
    {
        $entity   = $this->unitOfWork->createEntity($this->classMetadata->getName(), $data);

        return $entity;
    }

    /**
     * Unwraps the data from its envelopes
     *
     * @param array $data
     * @param array $envelopes
     *
     * @return array
     */
    private function unwrap(array $data, array $envelopes)
    {
        foreach ($envelopes as $envelope) {
            if (isset($data[$envelope])) {
                $data = $data[$envelope];
            }
        }

        return $data;
    }

    /**
     * @param array $data
     *
     * @return array
     */
    private function mapFromSerialized(array $data)
    {
        $mappedEntityData = array();

        foreach ($data as $serializedName => $value) {
            if ($this->classMetadata->hasField($this->classMetadata->getFieldName($serializedName))) {
                $fieldName    = $this->classMetadata->getFieldName($serializedName);
                $fieldMapping = $this->classMetadata->getFieldMapping($fieldName);

                if (isset($fieldMapping['association'])) {
                    $embedded = array();

                    $associationMetadata   = $this->classMetadataFactory->getMetadataFor($fieldMapping['targetEntity']);
                    $associationSerializer = new Serializer(
                        $associationMetadata,
                        $this->unitOfWork,
                        $this->classMetadataFactory
                    );

                    if ($fieldMapping['association'] === ClassMetadata::EMBED_ONE) {
                        if (is_array($value)) {
                            $associationData = $associationSerializer->mapFromSerialized($value);
                            $associationSerializer->hydrateSingleEntity($associationData, $embedded);

                            $value = reset($embedded);
                        } else {
                            $value = null;
                        }
                    }
                } else {
                    switch ($fieldMapping['type']) {
                        case 'string':
                            if (!is_null($value)) {
                                $value = (string) $value;
                            }
                            break;

                        case 'integer':
                            if (!is_null($value)) {
                                $value = (int) $value;
                            }
                            break;

                        case 'boolean':
                            if (!is_null($value)) {
                                $value = (bool) $value;
                            }
                            break;

                        /*case 'datetime':
                            if (!is_null($value)) {
                                $value = new \DateTime($value);
                            }
                            break;*/

                        // Add by Julien for Smile
                        case 'array':
                            if (!is_null($value)) {
                                $newValue = array();
                                foreach($value as $key => $content){
                                    $newValue[$key] = $content;
                                }
                                $value = $newValue;
                            }
                            break;
                        // End Add
                        // Add by Cédric for Smile
                        case 'datetime':
                            if (!is_null($value)) {
                                $value = new \Datetime($value);
                            }
                            break;

                        // End Add
                        default:
                            $value = $this->pluginMap($fieldMapping['type'], $value);
                    }
                }

                $mappedEntityData[$fieldName] = $value;
            }
        }

        return $mappedEntityData;
    }

    public function pluginMap($type, $value)
    {
        return ($this->serializedPlugin != null) ? $this->serializedPlugin->deserialize($type, $value) : null;
    }
}
